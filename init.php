<?php

use System\Classes\PluginManager;

// Extend the frontend controller to minify HTML with the Minify middleware.
\Cms\Classes\CmsController::extend(function($controller) {
    $controller->middleware('Arcane\Seo\Middleware\MinifyHtml');
});

// add js dependencies in the backend
\Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
    $controller->addJs('/plugins/arcane/seo/assets/arcane.seo.js');
    $controller->addJs('https://cdn.jsdelivr.net/npm/vue');
});


Arcane\Seo\Models\Settings::extend(function($model) {
    $model->bindEvent('model.afterSave', function() use ($model) {
        $htaccess = $model->value["htaccess"];
        File::put(base_path(".htaccess"), $htaccess);
    });
});


// make our Post fields jsonable
if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
{
    \RainLab\Blog\Models\Post::extend(function($model) {
        $model->addJsonable('arcane_seo_options');
    });
}
