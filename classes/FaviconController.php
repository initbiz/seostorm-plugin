<?php

namespace Initbiz\SeoStorm\Classes;

use Storage;
use File;
use Resizer;
use Cms\Classes\Controller;
use Initbiz\SeoStorm\Models\Settings;
echo('debugging');
class FaviconController
{
    public function index()
    {
        $settings = Settings::instance();
        dump($settings);
        //import favicon
        $favicon = ($settings->getOriginal('favicon'));
        //resize it according to repeater promptss
        foreach ($settings->getOriginal('favicon_repeater') as $size) {
            $favicon->resize($size, $size, ['mode' => 'fit']);
        }
        die;
        
        //$favicon->save('/storage/Favicon');

        //return to the webmanifest


        if (!$settings->favicon_enabled) {
            $controller = new Controller();
            $controller->setStatusCode(404);

            return $controller->run('/404');;
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
}
