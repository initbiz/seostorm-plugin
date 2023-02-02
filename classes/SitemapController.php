<?php

namespace Initbiz\SeoStorm\Classes;

use Response;
use Cms\Classes\Controller;
use Initbiz\SeoStorm\Models\Settings;

class SitemapController
{
    public function index()
    {
        $sitemap = new Sitemap();

        if (Settings::get('enable_sitemap')) {
            return Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
        }
    }
}
