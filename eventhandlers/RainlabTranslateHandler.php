<?php

namespace Initbiz\SeoStorm\EventHandlers;

use RainLab\Pages\Classes\Page;
use October\Rain\Database\Model;
use System\Classes\PluginManager;
use Initbiz\SeoStorm\Classes\StormedManager;

class RainlabTranslateHandler
{
    public function subscribe($event)
    {
        if (PluginManager::instance()->hasPlugin('RainLab.Translate')) {
            $this->addTranslatableSeoFields($event);
        }
    }

    protected function addTranslatableSeoFields($event)
    {
        $stormedManager = StormedManager::instance();
        foreach ($stormedManager->getStormedModels() as $stormedModelClass => $stormedModelDef) {
            if (!class_exists($stormedModelClass)) {
                continue;
            }

            $stormedModelClass::extend(function ($model) use ($stormedManager) {
                if (!$model->propertyExists('translatable')) {
                    $model->addDynamicProperty('translatable', []);
                }

                $model->translatable = array_merge($model->translatable, $stormedManager->seoFieldsToTranslate());

                /*
                * Add translation support to database models
                * We need to check if the database models implement all the
                * required behaviors
                */
                if ($model instanceof Model) {
                    $requiredBehaviors = [
                        'RainLab\Translate\Behaviors\TranslatableModel',
                        'October\Rain\Database\Behaviors\Purgeable',
                    ];

                    if (!isset($model->implement)) {
                        $model->implement = [];
                    }

                    foreach ($requiredBehaviors as $behavior) {
                        $behaviorFound = false;
                        foreach ($model->implement as $use) {
                            $use = str_replace('.', '\\', trim($use));
                            if ('@' . $behavior === $use || $behavior === $use) {
                                $behaviorFound = true;
                                break;
                            }
                        }
                        if (!$behaviorFound) {
                            $model->implement[] = $behavior;
                        }
                    }
                }
            });
        }
    }
}
