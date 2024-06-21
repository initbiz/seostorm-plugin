<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Cms\Classes\Page;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\SeoStorm\Classes\SitemapGenerator;

class SitemapHandler
{
    public function subscribe($event)
    {
        $this->afterSaveCmsPage($event);
    }

    public function afterSaveCmsPage($event): void
    {
        $event->listen('halcyon.saved: RainLab\Pages\Classes\Page', function ($model) {
            $sitemapGenerator = new SitemapGenerator();
            $pages = $sitemapGenerator->makeItemsCmsPage($model);
        });

        $event->listen('halcyon.saved: Cms\Classes\Page', function ($model) {
            SitemapItem::makeSitemapItemsForCmsPage($model);
        });
    }
}
