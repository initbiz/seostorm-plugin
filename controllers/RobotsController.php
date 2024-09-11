<?php

namespace Initbiz\SeoStorm\Controllers;

use Site;
use Twig;
use Response;
use Illuminate\Http\Request;
use Initbiz\SeoStorm\Models\Settings;

class RobotsController
{
    public function index(Request $request)
    {
        $site = Site::getSiteFromRequest($request->getSchemeAndHttpHost(), $request->getPathInfo());

        $vars = [
            'domain' => url('/'),
            'site' => $site,
        ];

        $content = Settings::get('robots_txt');
        $content = Twig::parse($content, $vars);

        $response = Response::make($content);
        $response->header('Content-Type', 'text/plain');
        return $response;
    }
}
