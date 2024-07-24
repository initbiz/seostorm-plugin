<?php

namespace Initbiz\SeoStorm\Classes;

use Route;
use Initbiz\SeoStorm\Models\Settings;

/**
 * SEO Storm router - will register routings for sitemaps, robots, etc.
 * if enabled in settings
 */
class Router
{
    public function register(): void
    {
        $settings = Settings::instance();

        if ($settings->get('enable_sitemap')) {
            $this->registerSitemapRouting();
        }

        if ($settings->get('enable_index_sitemap')) {
            $this->sitemapIndexRouting();
        }

        if ($settings->get('enable_index_sitemap_videos')) {
            $this->sitemapVideosRouting();
        }

        if ($settings->get('enable_index_sitemap_images')) {
            $this->sitemapImagesRouting();
        }

        $this->robotsRouting();
        $this->faviconRouting();
    }

    public function registerSitemapRouting(): void
    {
        $sites = Settings::instance()->getSitesEnabledInSitemap();
        foreach ($sites as $site) {
            $prefix = $site->is_prefixed ? $site->route_prefix : '';
            $sitemapUrl = $prefix . '/sitemap.xml';
            Route::get($sitemapUrl, [SitemapController::class, 'sitemap']);
        }
    }

    public function sitemapIndexRouting(): void
    {
        $sites = Settings::instance()->getSitesEnabledInSitemap();
        foreach ($sites as $site) {
            $prefix = $site->is_prefixed ? $site->route_prefix : '';
            $sitemapUrl = $prefix . '/sitemap_index.xml';
            Route::get($sitemapUrl, [SitemapController::class, 'index']);
        }
    }

    public function sitemapVideosRouting(): void
    {
        $sites = Settings::instance()->getSitesEnabledInSitemap();
        foreach ($sites as $site) {
            $prefix = $site->is_prefixed ? $site->route_prefix : '';
            $sitemapUrl = $prefix . '/sitemap_videos.xml';
            Route::get($sitemapUrl, [SitemapController::class, 'videos']);
        }
    }

    public function sitemapImagesRouting(): void
    {
        $sites = Settings::instance()->getSitesEnabledInSitemap();
        foreach ($sites as $site) {
            $prefix = $site->is_prefixed ? $site->route_prefix : '';
            $sitemapUrl = $prefix . '/sitemap_images.xml';
            Route::get($sitemapUrl, [SitemapController::class, 'images']);
        }
    }

    public function robotsRouting(): void
    {
        //TODO
    }

    public function faviconRouting(): void
    {
        //TODO
    }
}
