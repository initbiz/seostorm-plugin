<?php

namespace Initbiz\SeoStorm\SitemapGenerators;

use Initbiz\SeoStorm\Models\Settings;
use October\Rain\Support\Facades\Site;
use Initbiz\Sitemap\Generators\AbstractGenerator;
use Initbiz\Sitemap\DOMElements\SitemapDOMElement;
use Initbiz\Sitemap\DOMElements\SitemapIndexDOMElement;

class SitemapIndexGenerator extends AbstractGenerator
{
    public function makeDOMElements(): array
    {
        $activeSite = Site::getActiveSite();

        $sitemaps = [];
        if (Settings::get('enable_sitemap')) {
            $sitemap = new SitemapDOMElement();
            $sitemap->setLoc($activeSite->base_url . '/sitemap.xml');
            $sitemaps[] = $sitemap;
        }

        if (Settings::get('enable_index_sitemap_videos')) {
            $sitemap = new SitemapDOMElement();
            $sitemap->setLoc($activeSite->base_url . '/sitemap_videos.xml');
            $sitemaps[] = $sitemap;
        }

        if (Settings::get('enable_index_sitemap_images')) {
            $sitemap = new SitemapDOMElement();
            $sitemap->setLoc($activeSite->base_url . '/sitemap_images.xml');
            $sitemaps[] = $sitemap;
        }

        $sitemapIndexDOMElement = new SitemapIndexDOMElement();
        $sitemapIndexDOMElement->setSitemaps($sitemaps);

        return [$sitemapIndexDOMElement];
    }
}
