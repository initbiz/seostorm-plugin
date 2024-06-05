<?php

namespace Initbiz\SeoStorm\Classes;

use Response;

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

    public function videos()
    {
        $sitemap = new Sitemap();
        return Response::make($sitemap->generateVideos())->header('Content-Type', 'application/xml');
    }
    public function images()
    {
        $sitemap = new Sitemap();
        return Response::make($sitemap->generateImages())->header('Content-Type', 'application/xml');
    }
}
