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

        $pages = $pages
            ->filter(function ($page) {
                return $page->seoOptionsEnabledInSitemap;
            })->sortByDesc('seoOptionsPriority');

        foreach ($pages as $page) {
            // $page = Event::fire('initbiz.seostorm.generateSitemapCmsPage', [$page]);
            $modelClass = $page->seoOptionsModelClass;

            $loc = $page->url;

            $sitemapItem = new SitemapItem();
            $sitemapItem->priority = $page->seoOptionsPriority;
            $sitemapItem->changefreq = $page->seoOptionsChangefreq;
            $sitemapItem->loc = $loc;
            $sitemapItem->lastmod = $page->lastmod ?: Carbon::createFromTimestamp($page->mtime);

            // if page has model class
            if (class_exists($modelClass)) {
                $scope = $page->seoOptionsModelScope;
                if (empty($scope)) {
                    $models = $modelClass::all();
                } else {
                    $models = $modelClass::$scope()->get();
                }

                foreach ($models as $model) {
                    if (($model->seo_options['enabled_in_sitemap'] ?? null) === "0") {
                        continue;
                    }
                    $modelParams = $page->seoOptionsModelParams;
                    $loc = $page->url;

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
                            $loc = preg_replace($pattern, $replacement, $loc);
                        }
                    }

                    $sitemapItem->loc = $loc;

                    if ($page->seoOptionsUseUpdatedAt && isset($model->updated_at)) {
                        $sitemapItem->lastmod = $model->updated_at->format('c');
                    }

                    $this->addItemToSet($sitemapItem);
                }
            } else {
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
                $sitemapItem->loc = url($staticPage->url);
                $sitemapItem->lastmod = $viewBag->property('lastmod') ?: $staticPage->updated_at;
                $sitemapItem->priority = $viewBag->property('priority');
                $sitemapItem->changefreq = $viewBag->property('changefreq');

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
        $urlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        $xml->appendChild($urlSet);
        return $this->urlSet = $urlSet;
    }

    protected function makeRoot()
    {
        if ($this->xml !== null) {
            return $this->xml;
        }

        $xml = new \DOMDocument;
        $xml->encoding = 'UTF-8';

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
            $item->priority
        );

        if ($urlElement) {
            $urlSet->appendChild($urlElement);
        }

        return $urlSet;
    }

    protected function makeUrlElement($xml, $pageUrl, $lastModified, $frequency, $priority)
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

        return $url;
    }
}
