<?php

namespace Initbiz\SeoStorm\Classes;

use Event;
use Carbon\Carbon;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use Initbiz\SeoStorm\Classes\SitemapItem;

class  Sitemap
{
    /**
     * Maximum URLs allowed (Protocol limit is 50k)
     */
    const MAX_URLS = 50000;

    /**
     * Maximum generated URLs per type
     */
    const MAX_GENERATED = 10000;

    /**
     * @var integer A tally of URLs added to the sitemap
     */
    protected $urlCount = 0;

    protected $xml;

    protected $urlSet;

    public function generate($pages = [])
    {

        if (empty($pages)) {
            // get all pages of the current theme
            $pages = Page::listInTheme(Theme::getEditTheme());
        }

        $models = [];

        // initialise locale related vars, if available
        $translationsEnabled = (PluginManager::instance())->hasPlugin('RainLab.Translate');
        if ($translationsEnabled) {
            if(class_exists('RainLab\Translate\Models\Locale')) {
                $localeClass = '\RainLab\Translate\Models\Locale';
            } elseif(class_exists('RainLab\Translate\Classes\Locale')) {
                $localeClass = '\RainLab\Translate\Classes\Locale';
            }
            $locales = $localeClass::listEnabled();
            $defaultLocale = ($localeClass::getDefault())->code;
            $router = new \October\Rain\Router\Router;
        }

        $pages = $pages
            ->filter(function ($page) {
                return $page->seoOptionsEnabledInSitemap;
            })->sortByDesc('seoOptionsPriority');

        foreach ($pages as $page) {
            // $page = Event::fire('initbiz.seostorm.generateSitemapCmsPage', [$page]);
            $modelClass = $page->seoOptionsModelClass;

            if($translationsEnabled) {
                $page->rewriteTranslatablePageUrl($defaultLocale);
                $loc = url($router->urlFromPattern(sprintf("/%s%s", $defaultLocale, $page->url)));
            } else {
                $loc = $page->url;
            }

            $sitemapItem = new SitemapItem();
            $sitemapItem->priority = $page->seoOptionsPriority;
            $sitemapItem->changefreq = $page->seoOptionsChangefreq;
            $sitemapItem->loc = $loc;
            $sitemapItem->lastmod = $page->lastmod ?: Carbon::createFromTimestamp($page->mtime);

            if ($translationsEnabled) {
                foreach ($locales as $locale => $label) {
                    $page->rewriteTranslatablePageUrl($locale);
                    $sitemapItem->links[] = [
                        'rel' => 'alternate',
                        'hreflang' => $locale,
                        'href' => url($router->urlFromPattern(sprintf("/%s%s/", $locale, $page->url)))
                    ];
                }
            }

            // if page has model class
            if (class_exists($modelClass)) {
                $scope = $page->seoOptionsModelScope;

                if (empty($scope)) {
                    $models = $modelClass::all();
                } else {
                    $params = explode(':', $scope);
                    $models = $modelClass::{$params[0]}($params[1] ?? null)->get();
                }

                foreach ($models as $model) {
                    if (($model->seo_options['enabled_in_sitemap'] ?? null) === "0") {
                        continue;
                    }
                    $modelParams = $page->seoOptionsModelParams;

                    if (!empty($modelParams)) {
                        $modelParams = explode('|', $modelParams);
                        foreach ($modelParams as $modelParam) {
                            list($urlParam, $modelParam) = explode(':', $modelParam);

                            $pattern = '/:' . $urlParam . '\??/i';
                            $replacement = '';
                            if (strpos($modelParam, '.') === false) {
                                $replacement = $model->$modelParam;
                            } else {
                                // parameter with dot -> try to find by relation
                                list($relationMethod, $relatedAttribute) = explode('.', $modelParam);
                                if ($relatedObject = $model->$relationMethod()->first()) {
                                    $replacement = $relatedObject->$relatedAttribute ?? 'default';
                                }
                                $replacement = empty($replacement) ? 'default' : $replacement;
                            }
                            // Fill with parameters
                            $loc = preg_replace($pattern, $replacement, $loc);
                        }
                    }

                    $sitemapItem->loc = $this->trimOptionalParameters($loc);

                    if ($page->seoOptionsUseUpdatedAt && isset($model->updated_at)) {
                        $sitemapItem->lastmod = $model->updated_at->format('c');
                    }

                    $this->addItemToSet($sitemapItem);
                }
            } else {
                $sitemapItem->loc = $this->trimOptionalParameters($loc);
                $this->addItemToSet($sitemapItem);
            }
        }

        if (PluginManager::instance()->hasPlugin('RainLab.Pages')) {
            $staticPages = \RainLab\Pages\Classes\Page::listInTheme(Theme::getActiveTheme());
            foreach ($staticPages as $staticPage) {
                $viewBag = $staticPage->getViewBag();
                if (!$viewBag->property('enabled_in_sitemap')) {
                    continue;
                }

                $sitemapItem = new SitemapItem();
                if($translationsEnabled) {
                    $sitemapItem->loc = url($router->urlFromPattern(sprintf("/%s%s", $defaultLocale, $staticPage->url)));
                } else {
                    $sitemapItem->loc = url($staticPage->url);
                }
                $sitemapItem->lastmod = $viewBag->property('lastmod') ?: $staticPage->mtime;
                $sitemapItem->priority = $viewBag->property('priority');
                $sitemapItem->changefreq = $viewBag->property('changefreq');

                if ($translationsEnabled) {
                    $localeUrls = $viewBag->property('localeUrl', []);
                    foreach ($locales as $locale => $label) {
                        $url = array_key_exists($locale, $localeUrls) ? $localeUrls[$locale] : $staticPage->url;
                        $sitemapItem->links[] = [
                            'rel' => 'alternate',
                            'hreflang' => $locale,
                            'href' => url($router->urlFromPattern(sprintf("/%s%s", $locale, $url))),
                        ];
                    }
                }

                $this->addItemToSet($sitemapItem);
            }
        }

        $this->makeUrlSet();
        return $this->xml->saveXML();
    }

    protected function makeUrlSet()
    {
        if ($this->urlSet !== null) {
            return $this->urlSet;
        }

        $xml = $this->makeRoot();
        $urlSet = $xml->createElement('urlset');
        $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlSet->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $urlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        $xml->appendChild($urlSet);
        return $this->urlSet = $urlSet;
    }

    protected function makeRoot()
    {
        if ($this->xml !== null) {
            return $this->xml;
        }

        $xml = new \DOMDocument('1.0', 'UTF-8');

        return $this->xml = $xml;
    }

    protected function addItemToSet(SitemapItem $item)
    {
        $xml = $this->makeRoot();
        $urlSet = $this->makeUrlSet();

        try {
            $lastmod = new Carbon($item->lastmod);
        } catch (\Throwable $th) {
            $lastmod = new Carbon();
        }

        $urlElement = $this->makeUrlElement(
            $xml,
            url($item->loc), // make sure output is a valid url
            $lastmod->format('c'),
            $item->changefreq,
            $item->priority,
            $item->links
        );

        if ($urlElement) {
            $urlSet->appendChild($urlElement);
        }

        return $urlSet;
    }

    protected function makeUrlElement($xml, $pageUrl, $lastModified, $frequency, $priority, $links)
    {
        if ($this->urlCount >= self::MAX_URLS) {
            return false;
        }

        $this->urlCount++;

        $url = $xml->createElement('url');
        $pageUrl && $url->appendChild($xml->createElement('loc', $pageUrl));
        $lastModified && $url->appendChild($xml->createElement('lastmod', $lastModified));
        $frequency && $url->appendChild($xml->createElement('changefreq', $frequency));
        $priority && $url->appendChild($xml->createElement('priority', $priority));

        if (is_array($links) && count($links) > 0) {
            foreach ($links as $link) {
                $linkEl = $xml->createElement('xhtml:link');
                foreach ($link as $attr => $attrValue) {
                    $linkEl->setAttribute($attr, $attrValue);
                }
                $url->appendChild($linkEl);
            }
        }

        return $url;
    }

    /**
     * Remove optional parameters from URL - this method is used for last check
     * if the sitemap has an optional parameter left in the URL
     *
     * @param string $loc
     * @return string
     */
    protected function trimOptionalParameters(string $loc): string
    {
        // Remove empty optional parameters that don't have any models
        $pattern = '/\:.+\?/i';
        $loc = preg_replace($pattern, '', $loc);

        return $loc;
    }
}