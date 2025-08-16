<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\EventHandlers;

use Artisan;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\SeoStorm\Jobs\RefreshForCmsPageJob;
use Initbiz\SeoStorm\Jobs\UniqueQueueJobDispatcher;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;

class SitemapHandler
{
    /**
     * Storing themes codes to prevent from registering events twice
     *
     * @var array
     */
    private static array $themesWithEvents = [];

    public function subscribe($event)
    {
        // Prevent from registering these models when running migrations
        // Fix for blog posts seeder breaking creation
        if (($_SERVER['argv'][1] ?? "") === "october:migrate") {
            return;
        }

        $settings = Settings::instance();
        if ($settings->get('enable_sitemap')) {
            $this->halcyonModels($event);
            $this->seoStormedModels($event);
        }
    }

    public function halcyonModels($event): void
    {
        $event->listen('halcyon.saved: RainLab\Pages\Classes\Page', function ($model) {
            Artisan::call('queue:restart');

            $settings = Settings::instance();
            foreach ($settings->getSitesEnabledInSitemap() as $site) {
                $pagesGenerator = new PagesGenerator($site);
                $pagesGenerator->refreshForStaticPage($model);
            }
        });

        $event->listen('halcyon.saved: Cms\Classes\Page', function ($page) {
            $jobDispatcher = UniqueQueueJobDispatcher::instance();
            $jobDispatcher->push(RefreshForCmsPageJob::class, [
                'base_file_name' => $page->base_file_name,
            ]);
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
        $this->registerEventsInTheme($currentTheme);
    }

    /**
     * Register listeners on afterDelete and afterSave events on all CMS pages in the theme
     *
     * @param Theme $theme
     * @return void
     */
    public function registerEventsInTheme(Theme $theme): void
    {
        if (in_array($theme->getDirName(), self::$themesWithEvents, true)) {
            return;
        }

        self::$themesWithEvents[] = $theme->getDirName();

        $pages = Page::listInTheme($theme, true);
        foreach ($pages as $page) {
            $class = $page->seoOptionsModelClass ?? "";
            if (empty($class) || !class_exists($class)) {
                continue;
            }

            $class::extend(function ($model) use ($page) {
                $model->bindEvent('model.afterDelete', function () use ($page) {
                    $jobDispatcher = UniqueQueueJobDispatcher::instance();
                    $jobDispatcher->push(RefreshForCmsPageJob::class, [
                        'base_file_name' => $page->base_file_name,
                    ]);
                });
            });

            $class::extend(function ($model) use ($page) {
                $model->bindEvent('model.saveComplete', function () use ($page) {
                    $jobDispatcher = UniqueQueueJobDispatcher::instance();
                    $jobDispatcher->push(RefreshForCmsPageJob::class, [
                        'base_file_name' => $page->base_file_name,
                    ]);
                });
            });
        }
    }

    public static function clearCache(): void
    {
        self::$themesWithEvents = [];
    }
}
