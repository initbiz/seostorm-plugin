<?php

namespace Initbiz\SeoStorm\Classes;

use Site;
use Route;

class Router
{
    public static function handle(): void
    {
        $router = new self();
        $router->sitemapRouting();
        $router->sitemapIndexRouting();
        $router->robotsRouting();
        $router->faviconRouting();
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
}
