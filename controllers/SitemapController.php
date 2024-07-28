<?php

namespace Initbiz\SeoStorm\Controllers;

use Response;
use Initbiz\SeoStorm\Classes\SitemapImagesGenerator;
use Initbiz\SeoStorm\Classes\SitemapVideosGenerator;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;
use Initbiz\SeoStorm\SitemapGenerators\SitemapIndexGenerator;

class SitemapController
{
    public function index()
    {
        $generator = new SitemapIndexGenerator();
        return Response::make($generator->generate())->header('Content-Type', 'application/xml');
    }

    public function sitemap()
    {
        $generator = new PagesGenerator();
        return Response::make($generator->generate())->header('Content-Type', 'application/xml');
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
