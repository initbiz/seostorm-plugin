<?php

declare(strict_types=1);

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
