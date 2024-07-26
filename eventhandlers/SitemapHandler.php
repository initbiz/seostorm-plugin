<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Model;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Initbiz\Seostorm\Models\SitemapItem;

class SitemapHandler
{
    public function subscribe($event)
    {
        $this->afterSaveCmsPage($event);
        $this->afterUpdateModel($event);
    }

    public function afterSaveCmsPage($event): void
    {
        $event->listen('halcyon.saved: RainLab\Pages\Classes\Page', function ($model) {
            SitemapItem::refreshForStaticPage($model);
        });

        $event->listen('halcyon.saved: Cms\Classes\Page', function ($model) {
            SitemapItem::refreshForCmsPage($model);
        });
    }

    public function afterUpdateModel(): void
    {
        $currentTheme = Theme::getActiveTheme();
        $pagesObject = Page::listInTheme($currentTheme, true);
        foreach ($pagesObject as $page) {
            $class = $page->seoOptionsModelClass ?? "";
            if (empty($class) || !class_exists($class)) {
                continue;
            }

            $class::extend(function ($model) use ($page) {
                $model->bindEvent('model.afterSave', function () use ($page) {
                    SitemapItem::refreshForCmsPage($page);
                });
            });
        }
    }
}
