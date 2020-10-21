<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Initbiz\SeoStorm\Models\SeoOptions;

class SeoStormedModelsHandler
{
    public function subscribe($event)
    {
        $modelClasses = [
            '\RainLab\Blog\Models\Post',
        ];

        foreach ($modelClasses as $modelClass) {
            try {
                $model = new $modelClass;
            } catch (\Throwable $th) {
                continue;
            }

            $model->extend(function ($model) {
                if (!$model->isClassExtendedWith('Initbiz.SeoStorm.Behaviors.SeoStormed')) {
                    $model->extendClassWith('Initbiz.SeoStorm.Behaviors.SeoStormed');
                }
            });

            SeoOptions::extend(function($model) use ($modelClass) {
                $model->morphTo['seostorm_options'][] = [
                    $modelClass,
                    'name' => 'stormed',
                    'table' => 'initbiz_seostorm_seo_options'
                ];
            });
        }
    }
}
