<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Request;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\Seostorm\Models\SitemapItem;
use Illuminate\Http\Request as HttpRequest;
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
            $settings = Settings::instance();
            foreach ($settings->getSitesEnabledInSitemap() as $site) {
                SitemapItem::refreshForStaticPage($model, $site);
            }
        });

        $event->listen('halcyon.saved: Cms\Classes\Page', function ($model) {
            // We need to temporarily replace request with faked one to get valid URLs
            $originalRequest = Request::getFacadeRoot();
            $originalHost = parse_url($originalRequest->url())['host'];

            $request = new HttpRequest();
            $request->headers->set('host', $originalHost);

            Request::swap($request);

            $settings = Settings::instance();
            foreach ($settings->getSitesEnabledInSitemap() as $site) {
                try {
                    SitemapItem::refreshForCmsPage($model, $site);
                } catch (\Throwable $th) {
                    Request::swap($originalRequest);
                    trace_log($th->getMessage());
                    // In case of any issue in the page, we need to ignore it and proceed
                    continue;
                }
            }

            Request::swap($originalRequest);
        });

        $event->listen('halcyon.deleting: RainLab\Pages\Classes\Page', function ($model) {
            $pagesGenerator = new PagesGenerator();
            $settings = Settings::instance();
            foreach ($settings->getSitesEnabledInSitemap() as $site) {
                $item = $pagesGenerator->makeItemForStaticPage($model, $site);
                $item->delete();
            }
        });

        $event->listen('halcyon.deleting: Cms\Classes\Page', function ($model) {
            $originalRequest = Request::getFacadeRoot();
            $request = new HttpRequest();
            Request::swap($request);

            $pagesGenerator = new PagesGenerator();
            $settings = Settings::instance();
            foreach ($settings->getSitesEnabledInSitemap() as $site) {
                $items = $pagesGenerator->makeItemsForCmsPage($model, $site);
                foreach ($items as $item) {
                    try {
                        $item->delete();
                    } catch (\Throwable $th) {
                        Request::swap($originalRequest);
                        trace_log($th->getMessage());
                        // In case of any issue in the page, we need to ignore it and proceed
                        continue;
                    }
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
                        SitemapItem::refreshForCmsPage($page, $site);
                    }
                });
            });

            $class::extend(function ($model) use ($page) {
                $model->bindEvent('model.afterSave', function () use ($page) {
                    $settings = Settings::instance();
                    foreach ($settings->getSitesEnabledInSitemap() as $site) {
                        SitemapItem::refreshForCmsPage($page, $site);
                    }
                });
            });
        }
    }
}
