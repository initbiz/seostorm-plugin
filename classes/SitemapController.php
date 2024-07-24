<?php

namespace Initbiz\SeoStorm\Classes;

use Response;
use Initbiz\SeoStorm\Classes\SitemapImagesGenerator;
use Initbiz\SeoStorm\Classes\SitemapVideosGenerator;

class SitemapController
{
    public function index()
    {
        $sitemap = new SitemapGenerator();
        return Response::make($sitemap->generateIndex())->header('Content-Type', 'application/xml');
    }

    public function sitemap()
    {
        $sitemap = new SitemapGenerator();
        return Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
    }

    public function videos()
    {
        $sitemap = new SitemapVideosGenerator();
        return Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
    }

    public function images()
    {
        $sitemap = new SitemapImagesGenerator();
        return Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
    }
}
