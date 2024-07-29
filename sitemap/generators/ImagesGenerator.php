<?php

namespace Initbiz\SeoStorm\Sitemap\Generators;

use Site;
use System\Models\SiteDefinition;
use Initbiz\SeoStorm\Models\SitemapItem;
use Initbiz\Sitemap\DOMElements\UrlDOMElement;
use Initbiz\Sitemap\DOMElements\UrlsetDOMElement;
use Initbiz\Sitemap\Generators\AbstractGenerator;

class ImagesGenerator extends AbstractGenerator
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

        $sitemapItems = SitemapItem::with(['images'])->enabled()->whereHas('images')->withSite($site)->get();

        $urls = [];
        foreach ($sitemapItems as $sitemapItem) {
            $url = new UrlDOMElement();
            $url->setLoc($sitemapItem->loc);
            $images = [];
            foreach ($sitemapItem->images as $image) {
                $images[] = $image->toDOMElement();
            }

            $url->setImages($images);
            $urls[] = $url;
        }

        $urlSetDOMElement = new UrlsetDOMElement();
        $urlSetDOMElement->setUrls($urls);

        return [$urlSetDOMElement];
    }
}
