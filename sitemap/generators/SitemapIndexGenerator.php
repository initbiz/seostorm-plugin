<?php

namespace Initbiz\SeoStorm\Sitemap\Generators;

use System\Models\SiteDefinition;
use Initbiz\SeoStorm\Models\Settings;
use October\Rain\Support\Facades\Site;
use Initbiz\Sitemap\Generators\AbstractGenerator;
use Initbiz\Sitemap\DOMElements\SitemapDOMElement;
use Initbiz\Sitemap\DOMElements\SitemapIndexDOMElement;

class SitemapIndexGenerator extends AbstractGenerator
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

        $sitemaps = [];
        if (Settings::get('enable_sitemap')) {
            $sitemap = new SitemapDOMElement();
            $sitemap->setLoc($site->base_url . '/sitemap.xml');
            $sitemaps[] = $sitemap;
        }

        if (Settings::get('enable_videos_sitemap')) {
            $sitemap = new SitemapDOMElement();
            $sitemap->setLoc($site->base_url . '/sitemap_videos.xml');
            $sitemaps[] = $sitemap;
        }

        if (Settings::get('enable_images_sitemap')) {
            $sitemap = new SitemapDOMElement();
            $sitemap->setLoc($site->base_url . '/sitemap_images.xml');
            $sitemaps[] = $sitemap;
        }

        $sitemapIndexDOMElement = new SitemapIndexDOMElement();
        $sitemapIndexDOMElement->setSitemaps($sitemaps);

        return [$sitemapIndexDOMElement];
    }
}
