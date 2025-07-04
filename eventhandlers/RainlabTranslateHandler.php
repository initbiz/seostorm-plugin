<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Cms\Classes\Page;
use October\Rain\Database\Model;
use System\Classes\PluginManager;
use Initbiz\SeoStorm\Classes\StormedManager;
use RainLab\Pages\Classes\Page as StaticPage;

class RainlabTranslateHandler
{
    public function subscribe($event)
    {
        if (!PluginManager::instance()->exists('RainLab.Translate')) {
            return;
        }

        $event->listen('cms.beforeRoute', function () use ($event) {
            $this->addTranslatableSeoFields($event);
        });

        $this->addTranslatableSeoFieldsToEditor();

        if (PluginManager::instance()->exists('RainLab.Pages')) {
            $this->addTranslatableSeoFieldsToRainlabPages();
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
                $translatableFields = $stormedManager->addPrefix($stormedManager->getTranslatableSeoFieldsDefs(), 'seo_options', '%s[%s]');

                $model->translatable = array_merge($model->translatable, array_keys($translatableFields));

                /*
                * Add translation support to database models
                * We need to check if the database models implement all the
                * required behaviors
                */
                if ($model instanceof Model) {
                    $requiredBehaviors = [
                        'RainLab\Translate\Behaviors\TranslatableModel',
                        'Initbiz\SeoStorm\Behaviors\Purgeable',
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

    public function addTranslatableSeoFieldsToEditor()
    {
        Page::extend(function ($model) {
            if (!$model->propertyExists('translatable')) {
                $model->addDynamicProperty('translatable', []);
            }

            $stormedManager = StormedManager::instance();
            $fields = $stormedManager->addPrefix($stormedManager->getTranslatableSeoFieldsDefs(), 'seo_options', '%s_%s');

            foreach ($fields as $key => $fieldDef) {
                $newKey = camel_case($key);
                if (!in_array($newKey, $model->translatable)) {
                    $model->translatable[] = $newKey;
                }
            }
        });
    }

    public function addTranslatableSeoFieldsToRainlabPages()
    {
        StaticPage::extend(function ($model) {
            if (!$model->propertyExists('translatable')) {
                $model->addDynamicProperty('translatable', []);
            }

            $stormedManager = StormedManager::instance();
            $excludeFields = [
                'model_class',
                'model_scope',
                'model_params',
            ];

            $fields = $stormedManager->getTranslatableSeoFieldsDefs($excludeFields);
            $fields = $stormedManager->addPrefix($fields, 'viewBag');

            $model->translatable = array_unique(array_merge($model->translatable, array_keys($fields)));
        });
    }
}
