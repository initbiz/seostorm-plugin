<?php 

use Arcane\Seo\Classes\Robots;
use Arcane\Seo\Classes\Sitemap;
use Arcane\Seo\Models\Settings;
use Cms\Classes\Controller;
use October\Rain\Database\Attach\Resizer;
use File as FileHelper;


Route::get('robots.txt', function () {
    return Robots::response();
});

Route::get('sitemap.xml', function() {
    $sitemap = new Sitemap;
    if (! Settings::get('enable_sitemap'))
        return  \App::make(Controller::class)->setStatusCode(404)->run('/404');
        else
        return \Response::make($sitemap->generate())->header('Content-Type', 'application/xml');
        
    });
    
Route::get('favicon.ico', function() {
    $settings = Settings::instance();
    
    if (!$settings->favicon_enabled)
        return  \App::make(Controller::class)->setStatusCode(404)->run('/404');

    $finalPath = $inputPath = storage_path('app/media'.$settings->favicon);
    
    if ( $settings->favicon_16 ) {
        
        $destinationPath = storage_path('app/arcane/seo/favicon/' . dirname($settings->favicon).'/') ;
        $finalPath = $outputPath = $destinationPath . basename($settings->favicon) ;

        if (! file_exists($outputPath) ) {

            if (!FileHelper::makeDirectory($destinationPath, 0777, true, true) &&
                !FileHelper::isDirectory($destinationPath)
            ) { 
                trigger_error(error_get_last(), E_USER_WARNING); 
            }
    
            Resizer::open($inputPath)
                ->resize(16,16)
                ->save($outputPath);

            $finalPath = $outputPath;
        }
    }

    return response()->file($finalPath, [
        'Content-Type'=> 'image/x-icon',
    ]);
});
