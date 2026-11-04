<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Generators;

use System\Models\SiteDefinition;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\Sitemap\Generators\AbstractGenerator;
use Initbiz\Sitemap\DOMElements\SitemapDOMElement;
use Initbiz\Sitemap\DOMElements\SitemapIndexDOMElement;

class SitemapIndexGenerator extends AbstractGenerator
{
    /**
     * SiteDefinition
     *
     * @var SiteDefinition
     */
    protected SiteDefinition $site;

    public function __construct(SiteDefinition $site)
    {
        $this->site = $site;

        parent::__construct();
    }

    /**
     * Get the value of site
     *
     * @return SiteDefinition
     */
    public function getSite(): SiteDefinition
    {
        return $this->site;
    }

    /**
     * Make DOMElements listed in the sitemap
     *
     * @return array
     */
    public function makeDOMElements(): array
    {
        $site = $this->getSite();

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
