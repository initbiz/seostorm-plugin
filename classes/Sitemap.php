<?php

namespace Initbiz\SeoStorm\Classes;

use Event;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;

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

    public function generate()
    {
        // get all pages of the current theme
        $pages = Page::listInTheme(Theme::getEditTheme());
        $models = [];

        $pages = $pages
            ->filter(function ($page) {
                return $page->enabled_in_sitemap;
            })->sortByDesc('priority');

        foreach ($pages as $page) {
            // $page = Event::fire('initbiz.seostorm.generateSitemapCmsPage', [$page]);
            $modelClass = $page->model_class;

            // if page has model class
            if (class_exists($modelClass)) {
                $scope = $page->model_scope;
                if (empty($scope)) {
                    $models = $modelClass::all();
                } else {
                    $models = $modelClass::$scope()->get();
                }

                foreach ($models as $model) {
                    $modelParams = $page->model_params;
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
                                if ($relatedObject = $model->$relationMethod()) {
                                    $replacement = $relatedObject->$relatedAttribute;
                                }
                            }
                            $loc = preg_replace($pattern, $replacement, $loc);
                        }
                    }

                    $sitemapItem = new SitemapItem();
                    $use_updated = $page->use_updated_at;
                    $sitemapItem->loc = $loc;
                    $sitemapItem->lastmod = $use_updated ? $model->updated_at->format('c') : $page->lastmod;
                    $sitemapItem->priority = $page->priority;
                    $sitemapItem->changefreq = $page->changefreq;

                    $this->addItemToSet($sitemapItem);
                }
            } else {
                $this->addItemToSet(SitemapItem::asCmsPage($page));
            }
        }

        if (PluginManager::instance()->hasPlugin('RainLab.Pages')) {
            $staticPages = \RainLab\Pages\Classes\Page::listInTheme(Theme::getActiveTheme());
            foreach ($staticPages as $staticPage) {
                if (!$staticPage->getViewBag()->property('enabled_in_sitemap')) continue;
                $this->addItemToSet(SitemapItem::asStaticPage($staticPage));
            }
        }

        return $this->make();
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

    protected function addItemToSet(SitemapItem $item)
    {
        $xml = $this->makeRoot();
        $urlSet = $this->makeUrlSet();

        $urlElement = $this->makeUrlElement(
            $xml,
            url($item->loc), // make sure output is a valid url
            Helper::w3cDatetime($item->lastmod), // make sure output is a valid datetime
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

    protected function make()
    {
        $this->makeUrlSet();
        return $this->xml->saveXML();
    }
}
