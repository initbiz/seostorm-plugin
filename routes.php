<?php

namespace Initbiz\SeoStorm;

use App;
use File;
use Route;
use Resizer;
use Response;
use Cms\Classes\Controller;
use Initbiz\SeoStorm\Classes\Robots;
use Initbiz\SeoStorm\Classes\Sitemap;
use Initbiz\SeoStorm\Models\Settings;

Route::get('robots.txt', function () {
    return Robots::response();
});

Route::get('sitemap.xml', function () {
    $sitemap = new Sitemap();
    if (!Settings::get('enable_sitemap')) {
        return App::make(Controller::class)->setStatusCode(404)->run('/404');
    } else {
        return Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
    }
});

Route::get('favicon.ico', function () {
    $settings = Settings::instance();

    if (!$settings->favicon_enabled) {
        return App::make(Controller::class)->setStatusCode(404)->run('/404');
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
});
