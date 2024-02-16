<?php

namespace Initbiz\SeoStorm\Classes;

use File;
use Resizer;
use Response;
use Cms\Classes\Controller;
use Initbiz\SeoStorm\Models\Settings;

class FaviconController
{
    public function index()
    {
        $settings = Settings::instance();

        //favicons are part of the webmanifest
        //If the webmanifest is disabled controller should return 404
        if (!$settings->webmanifest_enabled) {
            $controller = new Controller();
            $controller->setStatusCode(404);

            return $controller->run('/404');
        }

        $finalPath = $inputPath = storage_path('app/media' . $settings->favicon);

        if ($settings->favicon_16) {

            $destinationPath = storage_path('app/initbiz/seostorm/favicon/' . dirname($settings->favicon) . '/');
            $finalPath = $outputPath = $destinationPath . basename($settings->favicon);

            if (!file_exists($outputPath)) {
                if (
                    !File::makeDirectory($destinationPath, 0777, true, true) &&
                    !File::isDirectory($destinationPath)
                ) {
                    trigger_error(error_get_last(), E_USER_WARNING);
                }

                Resizer::open($inputPath)->resize(16, 16)->save($outputPath);
                $finalPath = $outputPath;
            }
        }

        return response()->file($finalPath, [
            'Content-Type' => 'image/x-icon',
        ]);
    }

    public function generateManifest()
    {
        $settings = Settings::instance();
        $icons = [];

        if (!$settings->webmanifest_enabled) {
            return;
        }

        $favicon = $settings->favicon_fileupload;
        $sizes = array_column($settings->favicon_sizes, 'size');
        foreach ($sizes as $size) {
            $icons[] = [
                "src" => $favicon->getThumb($size, $size),
                "type" => "image/png",
                "sizes" => $size . "x" . $size,
            ];
            return Response::json(['icons' => $icons]);
        }
    }
}
