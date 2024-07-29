<?php

namespace Initbiz\SeoStorm\Controllers;

use Response;
use Illuminate\Http\Request;
use October\Rain\Support\Facades\Site;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;
use Initbiz\SeoStorm\Sitemap\Generators\ImagesGenerator;
use Initbiz\SeoStorm\Sitemap\Generators\VideosGenerator;
use Initbiz\SeoStorm\Sitemap\Generators\SitemapIndexGenerator;

class SitemapController
{
    public function index(Request $request)
    {
        $site = Site::getSiteFromRequest($request->getSchemeAndHttpHost(), $request->getPathInfo());
        Site::applyActiveSite($site);

        $generator = new SitemapIndexGenerator();
        return Response::make($generator->generate())->header('Content-Type', 'application/xml');
    }

    public function sitemap(Request $request)
    {
        $site = Site::getSiteFromRequest($request->getSchemeAndHttpHost(), $request->getPathInfo());
        Site::applyActiveSite($site);

        $generator = new PagesGenerator();
        return Response::make($generator->generate())->header('Content-Type', 'application/xml');
    }

    public function videos(Request $request)
    {
        $site = Site::getSiteFromRequest($request->getSchemeAndHttpHost(), $request->getPathInfo());
        Site::applyActiveSite($site);

        $sitemap = new VideosGenerator();
        return Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
    }

    public function images(Request $request)
    {
        $site = Site::getSiteFromRequest($request->getSchemeAndHttpHost(), $request->getPathInfo());
        Site::applyActiveSite($site);

        $sitemap = new ImagesGenerator();
        return Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
    }
}
