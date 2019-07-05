<?php 

use Arcane\Seo\Classes\Robots;
use Arcane\Seo\Classes\Sitemap;
use Arcane\Seo\Models\Settings;
use Cms\Classes\Controller;

Route::get('robots.txt', function () {
    return Robots::response();
});

Route::get('sitemap.xml', function() {
    $sitemap = new Sitemap;
    if (! Settings::get('enable_sitemap'))
        return  \App::make(Controller::class)->setStatusCode(404)->run('/404');
    else
        return \Response::make($sitemap->generate())->header('Content-Type', 'application/xml');

});