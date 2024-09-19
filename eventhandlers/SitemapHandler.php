<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;

class SitemapHandler
{
    public function subscribe($event)
    {
        $settings = Settings::instance();
        if ($settings->get('enable_sitemap')) {
            $this->halcyonModels($event);
            $this->seoStormedModels($event);
        }
    }

    public function halcyonModels($event): void
    {
        $event->listen('halcyon.saved: RainLab\Pages\Classes\Page', function ($model) {
            $settings = Settings::instance();
            foreach ($settings->getSitesEnabledInSitemap() as $site) {
                $pagesGenerator = new PagesGenerator($site);
                $pagesGenerator->refreshForStaticPage($model);
            }
        });

        $event->listen('halcyon.saved: Cms\Classes\Page', function ($model) {
            $settings = Settings::instance();
            foreach ($settings->getSitesEnabledInSitemap() as $site) {
                $pagesGenerator = new PagesGenerator($site);
                $pagesGenerator->refreshForCmsPage($model);
            }
        });

        $event->listen('halcyon.deleting: RainLab\Pages\Classes\Page', function ($staticPage) {
            $settings = Settings::instance();
            foreach ($settings->getSitesEnabledInSitemap() as $site) {
                $sitemapItems = SitemapItem::where('base_file_name', $staticPage->fileName)->withSite($site)->get();
                foreach ($sitemapItems as $sitemapItem) {
                    $sitemapItem->delete();
                }
            }
        });

        $event->listen('halcyon.deleting: Cms\Classes\Page', function ($model) {
            $settings = Settings::instance();
            foreach ($settings->getSitesEnabledInSitemap() as $site) {
                $pagesGenerator = new PagesGenerator($site);
                $items = $pagesGenerator->makeItemsForCmsPage($model);
                foreach ($items as $item) {
                    $item->delete();
                }
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
                    $settings = Settings::instance();
                    foreach ($settings->getSitesEnabledInSitemap() as $site) {
                        $pagesGenerator = new PagesGenerator($site);
                        $pagesGenerator->refreshForCmsPage($page);
                    }
                });
            });

            $class::extend(function ($model) use ($page) {
                $model->bindEvent('model.saveComplete', function () use ($page) {
                    $settings = Settings::instance();
                    foreach ($settings->getSitesEnabledInSitemap() as $site) {
                        $pagesGenerator = new PagesGenerator($site);
                        $pagesGenerator->refreshForCmsPage($page);
                    }
                });
            });
        }
    }
}
