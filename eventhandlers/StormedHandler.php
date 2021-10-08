<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Initbiz\SeoStorm\Models\SeoOptions;
use Initbiz\SeoStorm\Classes\StormedManager;

class StormedHandler
{
    /**
     * Array of model classes to extend
     *
     * @var array
     */
    protected $modelClasses = [];

    public function subscribe($event)
    {
        $this->extendModels($event);
    }

    protected function extendModels($event)
    {
        $stormedManager = StormedManager::instance();
        foreach ($stormedManager->getStormedModels() as $stormedModelClass => $stormedModelDef) {
            if (!class_exists($stormedModelClass)) {
                continue;
            }

            $stormedModelClass::extend(function ($model) use ($stormedManager) {
                $behaviorName = 'Initbiz.SeoStorm.Behaviors.SeoStormed';
                if (!$model->isClassExtendedWith($behaviorName)) {
                    $model->extendClassWith($behaviorName);
                }

                if (!isset($model->morphOne)) {
                    $model->addDynamicProperty('morphOne');
                }

                $morphOne = $model->morphOne;

                $morphOne['seostorm_options'] = [
                    SeoOptions::class,
                    'name' => 'stormed',
                    'table' => 'initbiz_seostorm_seo_options',
                ];

                $model->morphOne = $morphOne;
            });
        }
    }
}
