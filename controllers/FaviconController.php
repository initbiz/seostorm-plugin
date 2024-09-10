<?php

namespace Initbiz\SeoStorm\Controllers;

use Response;
use Initbiz\SeoStorm\Models\Settings;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FaviconController
{
    public function faviconIco(): BinaryFileResponse
    {
        $settings = Settings::instance();

        $favicon = $settings->getFaviconObject();

        return Response::file($favicon, ['Content-Type' => $favicon->getContentType()]);
    }
}
