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
        return Response::make($sitemap->generateIndex())->header('Content-Type', 'application/xml');
    }

    public function sitemap()
    {
        $sitemap = new Sitemap();
        return Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
    }
}
