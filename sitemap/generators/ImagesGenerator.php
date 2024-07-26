<?php

namespace Initbiz\SeoStorm\Sitemap\Generators;

use Site;
use DOMElement;
use System\Models\SiteDefinition;
use Initbiz\SeoStorm\Models\SitemapItem;
use Initbiz\SeoStorm\Sitemap\Generators\AbstractGenerator;
use Initbiz\SeoStorm\Sitemap\Resources\SitemapItemsCollection;

class SitemapImagesGenerator extends AbstractGenerator
{
    /**
     * Fill initial URL Set with proper attributes
     *
     * @param DOMElement $urlSet
     * @return DOMElement
     */
    public function fillUrlSet(DOMElement $urlSet): DOMElement
    {
        $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        $urlSet->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');

        return $urlSet;
    }

    /**
     * Make items that are added to the XML
     *
     * @param DOMElement $urlSet
     * @return SitemapItemsCollection
     */
    public function makeItems(?SiteDefinition $site = null): SitemapItemsCollection
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $sitemapItemsModel = SitemapItem::enabled()->whereHas('images')->withSite($site)->get();

        $sitemapItems = [];
        foreach ($sitemapItemsModel as $sitemapItemModel) {
            if (!$sitemapItemModel->isAvailable()) {
                continue;
            }

            $sitemapItem = new SitemapItem();
            $sitemapItem->loc = $sitemapItemModel->loc;
            foreach ($sitemapItemModel->media as $media) {
                $sitemapItem->images[] = $media->values;
            }
            $sitemapItems[] = $sitemapItem;
        }

        return new SitemapItemsCollection($sitemapItems);
    }
}
