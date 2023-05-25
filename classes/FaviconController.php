<?php

namespace Initbiz\SeoStorm\Classes;

use Response;
use Cms\Classes\Controller;
use Initbiz\SeoStorm\Models\Settings;

class FaviconController
{
    public function index2()
    {
        $settings = Settings::instance();

        if (!$settings->webmanifest_enabled) {
            $controller = new Controller();
            $controller->setStatusCode(404);

            return $controller->run('/404');;
        }

        if (!$settings->favicon_enabled) {
            $controller = new Controller();
            $controller->setStatusCode(404);

            return $controller->run('/404');;
        }

        $finalPath = $inputPath = storage_path('app/media' . $settings->favicon);

        return response()->file($finalPath, [
            'Content-Type' => 'image/x-icon',
        ]);
    }

    public function generateManifest()
    {
        $settings = Settings::instance();

        if (false) {
            // TODO: this should work only for fileupload
        }

        $responseArray = [];

        $favicon = $settings->favicon_fileupload;
        $sizes = array_column($settings->favicon_sizes, 'size');
        foreach ($sizes as $size) {
            $responseArray[] = [
                "src" => $favicon->getThumb($size, $size),
                "type" => "image/png",
                "sizes" => $size . "x" . $size,
            ];
        }

        return Response::json(['icons' => $responseArray]);
    }
}
