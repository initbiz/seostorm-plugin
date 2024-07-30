<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;

class SitemapHandler
{
    public function subscribe($event)
    {
        $this->halcyonModels($event);
        $this->seoStormedModels($event);
    }

    public function halcyonModels($event): void
    {
        $event->listen('halcyon.saved: RainLab\Pages\Classes\Page', function ($model) {
            SitemapItem::refreshForStaticPage($model);
        });

        $event->listen('halcyon.saved: Cms\Classes\Page', function ($model) {
            SitemapItem::refreshForCmsPage($model);
        });

        $event->listen('halcyon.deleting: RainLab\Pages\Classes\Page', function ($model) {
            SitemapItem::refreshForStaticPage($model);
            $pagesGenerator = new PagesGenerator();
            $item = $pagesGenerator->makeItemForStaticPage($model);
            $item->delete();
        });

        $event->listen('halcyon.deleting: Cms\Classes\Page', function ($model) {
            $pagesGenerator = new PagesGenerator();
            $items = $pagesGenerator->makeItemsForCmsPage($model);
            foreach ($items as $item) {
                $item->delete();
            }
        });
    }

    public function seoStormedModels($event): void
    {
        $currentTheme = Theme::getActiveTheme();
        $pages = Page::listInTheme($currentTheme, true);
        foreach ($pages as $page) {
            $class = $page->seoOptionsModelClass ?? "";
            if (empty($class) || !class_exists($class)) {
                continue;
            }

            $class::extend(function ($model) use ($page) {
                $model->bindEvent('model.afterDelete', function () use ($page) {
                    SitemapItem::refreshForCmsPage($page);
                });
            });

            $class::extend(function ($model) use ($page) {
                $model->bindEvent('model.afterSave', function () use ($page) {
                    SitemapItem::refreshForCmsPage($page);
                });
            });
        }
    }
}
