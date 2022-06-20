<?php

namespace Initbiz\SeoStorm;

use Route;
use Initbiz\SeoStorm\Classes\RobotsController;
use Initbiz\SeoStorm\Classes\SitemapController;
use Initbiz\SeoStorm\Classes\FaviconController;

Route::get('robots.txt', [RobotsController::class, 'index']);

Route::get('sitemap.xml', [SitemapController::class, 'index']);

Route::get('favicon.ico', [FaviconController::class, 'index']);
