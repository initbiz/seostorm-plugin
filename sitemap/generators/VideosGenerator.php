<?php

namespace Initbiz\SeoStorm\Sitemap\Generators;

use Site;
use System\Models\SiteDefinition;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\Sitemap\DOMElements\UrlDOMElement;
use Initbiz\Sitemap\DOMElements\UrlsetDOMElement;
use Initbiz\Sitemap\Generators\AbstractGenerator;

class VideosGenerator extends AbstractGenerator
{
    /**
     * Make DOMElements listed in the sitemap
     *
     * @param SiteDefinition|null $site
     * @return array
     */
    public function makeDOMElements(?SiteDefinition $site = null): array
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $sitemapItems = SitemapItem::with(['videos'])
            ->enabled()
            ->whereHas('videos')
            ->withSite($site)
            ->get();

        $urlDOMElements = [];
        foreach ($sitemapItems as $sitemapItem) {
            $urlDOMElement = new UrlDOMElement();
            $urlDOMElement->setLoc($sitemapItem->loc);
            $videosDOMElements = [];
            foreach ($sitemapItem->videos as $video) {
                $videosDOMElements[] = $video->toDOMElement();
            }

            $urlDOMElement->setVideos($videosDOMElements);
            $urlDOMElements[] = $urlDOMElement;
        }

        $urlSetDOMElement = new UrlsetDOMElement();
        $urlSetDOMElement->setUrls($urlDOMElements);

        return [$urlSetDOMElement];
    }
}
