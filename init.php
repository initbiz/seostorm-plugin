<?php

use System\Classes\PluginManager;

// Extend the frontend controller to minify HTML with the Minify middleware.
\Cms\Classes\CmsController::extend(function($controller) {
    $controller->middleware('Arcane\Seo\Middleware\Minify');
});

// add js dependencies in the backend
\Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
    $controller->addJs('/plugins/arcane/seo/assets/arcane.seo.js');
});


// make Blog fields jsonable
if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
{
    \RainLab\Blog\Models\Post::extend(function($model) {
        $model->addJsonable('arcane_seo_options');
    });
}