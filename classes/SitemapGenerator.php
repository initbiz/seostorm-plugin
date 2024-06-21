<?php

namespace Initbiz\SeoStorm\Classes;

use Carbon\Carbon;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use Initbiz\SeoStorm\Models\Settings;
use October\Rain\Support\Facades\Site;
use Initbiz\SeoStorm\Classes\SitemapItem;
use RainLab\Translate\Classes\Translator;
use RainLab\Pages\Classes\Page as StaticPage;
use Initbiz\Seostorm\Models\SitemapItem as ModelSitemapItem;

class SitemapGenerator
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

    protected $sitemapIndex;

    protected $sitemapItemModels;

    public function generate($pages = [])
    {
        $request = \Request::instance();
        $activeSite = Site::getSiteFromRequest($request->getSchemeAndHttpHost(), $request->getPathInfo());
        Site::applyActiveSite($activeSite);

        if (empty($pages)) {
            // get all pages of the current theme
            $pages = Page::listInTheme(Theme::getEditTheme());
        }
        $this->makeItemsCmsPages($pages);

        if (PluginManager::instance()->hasPlugin('RainLab.Pages')) {
            $staticPages = \RainLab\Pages\Classes\Page::listInTheme(Theme::getActiveTheme());
            $this->makeItemsStaticPages($staticPages);
        }

        $this->makeUrlSet();
        return $this->xml->saveXML();
    }

    public function generateIndex()
    {
        $xml = $this->makeRoot();
        $localesSitemap = $this->makeSitemapIndex();
        $request = \Request::instance();
        $activeSite = Site::getSiteFromRequest($request->getSchemeAndHttpHost(), $request->getPathInfo());
        if (Settings::get('enable_index_sitemap_videos')) {
            $sitemapElement = $localesSitemap->appendChild($xml->createElement('sitemap'));
            $sitemapElement->appendChild($xml->createElement('loc', $activeSite->base_url . '/sitemap_videos.xml'));
        }
        if (Settings::get('enable_index_sitemap_images')) {
            $sitemapElement = $localesSitemap->appendChild($xml->createElement('sitemap'));
            $sitemapElement->appendChild($xml->createElement('loc', $activeSite->base_url . '/sitemap_images.xml'));
        }

        $sitemapElement = $localesSitemap->appendChild($xml->createElement('sitemap'));
        $sitemapElement->appendChild($xml->createElement('loc', $activeSite->base_url . '/sitemap.xml'));
        $activeSite = Site::getActiveSite();

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

        $settings = Settings::instance();
        if ($settings->get('enable_image_in_sitemap')) {
            $urlSet->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
        }
        if ($settings->get('enable_video_in_sitemap')) {
            $urlSet->setAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');
        }
        $xml->appendChild($urlSet);

        return $this->urlSet = $urlSet;
    }

    public function makeSitemapIndex()
    {
        if ($this->sitemapIndex !== null) {
            return $this->sitemapIndex;
        }

        $xml = $this->makeRoot();
        $sitemapIndex = $xml->createElement('sitemapindex');
        $sitemapIndex->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $xml->appendChild($sitemapIndex);

        return $this->sitemapIndex = $sitemapIndex;
    }

    public function makeRoot()
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
        if ($this->getUrlsCount() >= self::MAX_URLS) {
            return false;
        }

        $urlSet = $this->makeUrlSet();
        $urlElement = $item->makeUrlElement($this->makeRoot());

        if ($urlElement) {
            $urlSet->appendChild($urlElement);
            $this->urlCount++;
        }

        return $urlSet;
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

    public function makeItemsCmsPages($pages): void
    {
        $pages = $pages
            ->filter(function ($page) {
                return $page->seoOptionsEnabledInSitemap;
            })->sortByDesc('seoOptionsPriority');

        $settings = Settings::instance();
        if ($settings->get('enable_image_in_sitemap') || $settings->get('enable_video_in_sitemap')) {
            $this->sitemapItemModels = ModelSitemapItem::get(['videos', 'images', 'loc'])->keyBy('loc')->toArray();
        }

        foreach ($pages as $page) {
            $this->makeItemsCmsPage($page);
        }
    }

    public function makeItemsCmsPage($page)
    {
        $sitemapItems = [];
        $sitemapItem = new SitemapItem();
        $sitemapItem->priority = $page->seoOptionsPriority;
        $sitemapItem->changefreq = $page->seoOptionsChangefreq;
        $sitemapItem->loc = $page->url;
        $sitemapItem->lastmod = $page->lastmod ?: Carbon::createFromTimestamp($page->mtime);
        $loc = $page->url;
        $baseFileName = $page->base_file_name;
        if (PluginManager::instance()->hasPlugin('RainLab.Translate')) {
            $translator = Translator::instance();
            $loc = $translator->getPageInLocale($baseFileName) ?? $loc;
        }

        $modelClass = $page->seoOptionsModelClass;
        // if page has model class
        if (class_exists($modelClass)) {
            $scope = $page->seoOptionsModelScope;
            $models = $this->getModels($modelClass, $scope);

            foreach ($models as $model) {
                if (($model->seo_options['enabled_in_sitemap'] ?? null) === "0") {
                    continue;
                }
                $modelParams = $page->seoOptionsModelParams;

                $loc = $this->getLocForModel($model, $modelParams, $baseFileName);

                $sitemapItem->loc = $this->trimOptionalParameters($loc);

                if ($page->seoOptionsUseUpdatedAt && isset($model->updated_at)) {
                    $sitemapItem->lastmod = $model->updated_at->format('c');
                }

                $this->makeItemMediaFromPage($sitemapItem);

                $sitemapItems[] = $sitemapItem->toArray();
                $this->addItemToSet($sitemapItem);
            }
        } else {
            $this->makeItemMediaFromPage($sitemapItem);

            $sitemapItem->loc = $this->trimOptionalParameters($loc);
            $sitemapItems[] = $sitemapItem->toArray();
            $this->addItemToSet($sitemapItem);
        }

        return $sitemapItems;
    }

    public function makeItemsStaticPages($staticPages): void
    {
        foreach ($staticPages as $staticPage) {
            $viewBag = $staticPage->getViewBag();
            if (!$viewBag->property('enabled_in_sitemap')) {
                continue;
            }

            $sitemapItem = new SitemapItem();
            $sitemapItem->loc = StaticPage::url($staticPage->fileName);
            $sitemapItem->lastmod = $viewBag->property('lastmod') ?: $staticPage->mtime;
            $sitemapItem->priority = $viewBag->property('priority');
            $sitemapItem->changefreq = $viewBag->property('changefreq');

            $this->makeItemMediaFromPage($sitemapItem);

            $this->addItemToSet($sitemapItem);
        }
    }

    public function makeItemMediaFromPage(SitemapItem $sitemapItem): void
    {
        if (!isset($this->sitemapItemModels[url($sitemapItem->loc)])) {
            return;
        }

        $modelSitemapItem = $this->sitemapItemModels[url($sitemapItem->loc)];

        $settings = Settings::instance();
        if ($settings->get('enable_image_in_sitemap')) {
            $sitemapItem->images = $modelSitemapItem['images'] ?? [];
        }

        if ($settings->get('enable_video_in_sitemap')) {
            $sitemapItem->videos = $modelSitemapItem['videos'] ?? [];
        }
    }

    public function getUrlsCount()
    {
        return $this->urlCount;
    }

    public function getModels($modelClass, $scope)
    {
        if (empty($scope)) {
            return $modelClass::all();
        } else {
            $params = explode(':', $scope);
            return $modelClass::{$params[0]}($params[1] ?? null)->get();
        }
    }

    public function getLocForModel($model, $modelParams, $baseFileName): string
    {
        if (empty($modelParams)) {
            return \Cms::pageUrl($baseFileName);
        }

        $params = [];
        $modelParams = explode('|', $modelParams);
        foreach ($modelParams as $modelParam) {
            list($urlParam, $modelParam) = explode(':', $modelParam);

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
            $params[$urlParam] = $replacement;
        }
        if (PluginManager::instance()->hasPlugin('RainLab.Translate')) {
            $translator = Translator::instance();
            $loc = $translator->getPageInLocale($baseFileName, null, $params);
        } else {
            $loc = \Cms::pageUrl($baseFileName, $params);
        }

        return $loc;
    }
}
