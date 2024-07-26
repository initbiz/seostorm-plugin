<?php

namespace Initbiz\SeoStorm\Sitemap\Generators;

use Site;
use Initbiz\SeoStorm\Classes\SitemapItem;
use Initbiz\SeoStorm\Classes\SitemapGenerator;
use Initbiz\Seostorm\Models\SitemapItem as ModelSitemapItem;

class SitemapImagesGenerator extends SitemapGenerator
{
    protected $sitemapItemModels;

    protected function fillUrlSet()
    {
        if ($this->urlSet !== null) {
            return $this->urlSet;
        }

        $xml = $this->getXml();
        $urlSet = $xml->createElement('urlset');
        $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        $urlSet->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');

        $xml->appendChild($urlSet);

        return $this->urlSet = $urlSet;
    }

    public function makeItems($pages = []): void
    {
        $site = Site::getActiveSite();
        $sitemapItemsModel = ModelSitemapItem::where('site_definition_id', $site->id)->whereHas('media', function ($query) {
            $query->where('type', 'image');
        })->with('media')->get();

        foreach ($sitemapItemsModel as $sitemapItemModel) {
            if (!$sitemapItemModel->isAvailable()) {
                continue;
            }

            $sitemapItem = new SitemapItem();
            $sitemapItem->loc = $sitemapItemModel->loc;
            foreach ($sitemapItemModel->media as $media) {
                $sitemapItem->images[] = $media->values;
            }
            $this->addItemToSet($sitemapItem);
        }
    }
}
