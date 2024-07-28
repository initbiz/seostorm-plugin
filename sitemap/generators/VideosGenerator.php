<?php

namespace Initbiz\SeoStorm\SitemapGenerators;

use Site;
use DOMElement;
use System\Models\SiteDefinition;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\SeoStorm\Sitemap\Generators\AbstractGenerator;

class SitemapVideosGenerator extends AbstractGenerator
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
        $urlSet->setAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');

        return $urlSet;
    }

    /**
     * Make items that are added to the XML
     *
     * @param SiteDefinition|null $site
     * @return array
     */
    public function makeItems(?SiteDefinition $site = null): array
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $sitemapItemsModel = SitemapItem::enabled()->whereHas('videos')->withSite($site)->get();

        $sitemapItems = [];
        foreach ($sitemapItemsModel as $sitemapItemModel) {
            $sitemapItem = new SitemapItem();
            $sitemapItem->loc = $sitemapItemModel->loc;
            foreach ($sitemapItemModel->media as $media) {
                if ($media->type === 'video') {
                    $sitemapItem->videos[] = $media->values;
                }
            }
            $sitemapItems[] = $sitemapItem;
        }

        return $sitemapItems;
    }
}
