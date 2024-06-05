<?php

namespace Initbiz\SeoStorm\Classes;

use Site;
use Route;
use Initbiz\SeoStorm\Models\Settings;

class Router
{
    private Settings $settings;

    public function handle(): void
    {
        $settings = Settings::instance();

        if ($settings->get('enable_sitemap')) {
            $this->sitemapRouting();
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

    public function sitemapRouting(): void
    {
        Route::get('sitemap.xml', [SitemapController::class, 'sitemap']);
    }

    public function robotsRouting(): void
    {
    }

    public function faviconRouting(): void
    {
    }

    public function sitemapIndexRouting(): void
    {
        $sites = Site::listEnabled();
        foreach ($sites as $site) {
            $prefix = $site->is_prefixed ? $site->route_prefix : '';
            $sitemapUrl = $prefix . '/sitemap_index.xml';
            Route::get($sitemapUrl, [SitemapController::class, 'index']);
        }
    }

    public function sitemapVideosRouting(): void
    {
        $sites = Site::listEnabled();
        foreach ($sites as $site) {
            $prefix = $site->is_prefixed ? $site->route_prefix : '';
            $sitemapUrl = $prefix . '/sitemap_videos.xml';
            Route::get($sitemapUrl, [SitemapController::class, 'videos']);
        }
    }

    public function sitemapImagesRouting(): void
    {
        $sites = Site::listEnabled();
        foreach ($sites as $site) {
            $prefix = $site->is_prefixed ? $site->route_prefix : '';
            $sitemapUrl = $prefix . '/sitemap_images.xml';
            Route::get($sitemapUrl, [SitemapController::class, 'images']);
        }
    }

    public function getSettings(): Settings
    {
        if ($this->settings) {
            return $this->settings;
        }
        
        return $this->settings = Settings::instance() ;
    }
}
