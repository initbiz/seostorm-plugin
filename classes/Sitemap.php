<?php

namespace Initbiz\SeoStorm\Classes;

use Cms\Classes\Page;
use Cms\Classes\Theme;

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

    private $xml;
    private $urlSet;

    function generate()
    {
        // get all pages of the current theme
        $pages = Page::listInTheme(Theme::getEditTheme());
        $models = [];

        foreach ($pages as $page) {
            if (!$page->enabled_in_sitemap) continue;

            $modelClass = $page->model_class;

            // if page has model class
            if (class_exists($modelClass)) {
                $models = $modelClass::all();

                foreach ($models as $model) {
                    if ($page->hasComponent('blogPost')) {
                        if (!(int)$model->seo_options['enabled_in_sitemap']) {
                             continue;
                        }
                        $this->addItemToSet(SitemapItem::asPost($page, $model));
                    } else {
                        $this->addItemToSet(SitemapItem::asCmsPage($page, $model));
                    }
                }
            } else {
                $this->addItemToSet(SitemapItem::asCmsPage($page));
            }
        }

        // if RainLab.Pages is installed
        if (Helper::rainlabPagesExists()) {
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

    protected function addItemToSet(SitemapItem $item, $url = null, $mtime = null)
    {
        $xml = $this->makeRoot();
        $urlSet = $this->makeUrlSet();

        $urlElement = $this->makeUrlElement(
            $xml,
            url($item->loc), // make sure output is a valid url
            Helper::w3cDatetime($item->lastmod), // make sure output is  a valid datetime
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
