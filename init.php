<?php

namespace Initbiz\SeoStorm;

use System\Classes\PluginManager;

// Extend the frontend controller to minify HTML with the Minify middleware.
\Cms\Classes\CmsController::extend(function($controller) {
    $controller->middleware('Initbiz\SeoStorm\Middleware\MinifyHtml');
});

// add js dependencies in the backend
\Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
    $controller->addJs('/plugins/initbiz/seostorm/assets/initbiz.seostorm.js');
});

\Initbiz\SeoStorm\Models\Settings::extend(function($model) {
    $model->bindEvent('model.afterSave', function() use ($model) {
        $htaccess = $model->value["htaccess"];
        \File::put(base_path(".htaccess"), $htaccess);
    });
});

\Event::subscribe(\Initbiz\SeoStorm\EventHandlers\SeoStormedModelsHandler::class);
