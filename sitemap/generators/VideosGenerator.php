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

        $sitemapItems = SitemapItem::with(['videos'])->enabled()->whereHas('videos')->withSite($site)->get();

        $urls = [];
        foreach ($sitemapItems as $sitemapItem) {
            $url = new UrlDOMElement();
            $url->setLoc($sitemapItem->loc);
            $videos = [];
            foreach ($sitemapItem->videos as $video) {
                $videos[] = $video->toDOMElement();
            }

            $url->setVideos($videos);
            $urls[] = $url;
        }

        $urlSetDOMElement = new UrlsetDOMElement();
        $urlSetDOMElement->setUrls($urls);

        return [$urlSetDOMElement];
    }
}
