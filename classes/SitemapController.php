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

        if (!Settings::get('enable_sitemap')) {
            $controller = new Controller();
            $controller->setStatusCode(404);

            return $controller->run('/404');
        } else {
            return Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
        }
    }
}
