<?php

namespace Initbiz\SeoStorm\Classes;

use System\Classes\PluginManager;
use Initbiz\SeoStorm\Classes\SitemapItem;
use RainLab\Translate\Classes\Translator;
use RainLab\Pages\Classes\Page as StaticPage;
use Initbiz\SeoStorm\Classes\SitemapGenerator;
use Initbiz\Seostorm\Models\SitemapItem as ModelSitemapItem;

class SitemapVideosGenerator extends SitemapGenerator
{
    protected $sitemapItemModels;

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
        $urlSet->setAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');

        $xml->appendChild($urlSet);

        return $this->urlSet = $urlSet;
    }

    public function makeItemsCmsPages($pages): void
    {
        $this->sitemapItemModels = ModelSitemapItem::get(['videos', 'loc'])->pluck('videos', 'loc')->toArray();

        parent::makeItemsCmsPages($pages);
    }

    public function makeItemsCmsPage($page)
    {
        $sitemapItems = [];
        $sitemapItem = new SitemapItem();
        $sitemapItem->loc = $page->url;
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

                $this->makeItemMediaFromPage($sitemapItem);

                $sitemapItems[] = $sitemapItem->toArray();
                if (!empty($sitemapItem->videos)) {
                    $this->addItemToSet($sitemapItem);
                }
            }
        } else {
            $this->makeItemMediaFromPage($sitemapItem);

            $sitemapItem->loc = $this->trimOptionalParameters($loc);
            $sitemapItems[] = $sitemapItem->toArray();
            if (!empty($sitemapItem->videos)) {
                $this->addItemToSet($sitemapItem);
            }
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
            $this->makeItemMediaFromPage($sitemapItem);
            if (!empty($sitemapItem->videos)) {
                $this->addItemToSet($sitemapItem);
            }
        }
    }

    public function makeItemMediaFromPage(SitemapItem $sitemapItem): void
    {
        $videos = $this->sitemapItemModels[url($sitemapItem->loc)];
        $sitemapItem->videos = $videos ?? [];
    }
}
