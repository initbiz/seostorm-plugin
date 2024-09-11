<?php

namespace Initbiz\SeoStorm\Controllers;

use Response;
use System\Models\File;
use Initbiz\SeoStorm\Models\Settings;

class FaviconController
{
    public function faviconIco()
    {
        $settings = Settings::instance();

        $favicon = $settings->getFaviconObject();

        if ($favicon instanceof File) {
            return Response::file($favicon->getLocalPath(), ['Content-Type' => $favicon->getContentType()]);
        }

        return Response::make('Not found', 404);
    }
}
