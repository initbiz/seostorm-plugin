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

        $sitemapItems = SitemapItem::with(['images'])
            ->enabled()
            ->whereHas('images')
            ->withSite($site)
            ->get();

        $urlDOMElements = [];
        foreach ($sitemapItems as $sitemapItem) {
            $urlDOMElement = new UrlDOMElement();
            $urlDOMElement->setLoc($sitemapItem->loc);
            $imagesDOMElements = [];
            foreach ($sitemapItem->images as $image) {
                $imagesDOMElements[] = $image->toDOMElement();
            }

            $urlDOMElement->setImages($imagesDOMElements);
            $urlDOMElements[] = $urlDOMElement;
        }

        $urlSetDOMElement = new UrlsetDOMElement();
        $urlSetDOMElement->setUrls($urlDOMElements);

        return [$urlSetDOMElement];
    }
}
