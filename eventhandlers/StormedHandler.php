<?php

namespace Initbiz\SeoStorm\EventHandlers;

use App;
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
        if (App::runningInBackend()) {
            $this->extendFormWidgets($event);
        }
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

    protected function extendFormWidgets($event)
    {
        /**
         * This applies to the new version of octobercms
         * Adds a button in the page toolbar editor
         */
        $event->listen('cms.template.getTemplateToolbarSettingsButtons', function ($extension, $dataHolder) {
            if ($dataHolder->templateType === 'page') {
                $stormedManager = StormedManager::instance();

                $dataHolder->buttons[] = [
                    'button' => 'initbiz.seostorm::lang.plugin.name',
                    'icon' => 'icon-search',
                    'popupTitle' => 'initbiz.seostorm::lang.plugin.name',
                    'useViewBag' => false,
                    'properties' => $stormedManager->getSeoFieldsDefsForEditor()
                ];
            }
        });

        /**
         * This applies to all other seostormed models
         */
        $event->listen('backend.form.extendFieldsBefore', function ($widget) {
            $stormedManager = StormedManager::instance();
            foreach ($stormedManager->getStormedModels() as $stormedModelClass => $stormedModelDef) {
                if ($widget->isNested === false && $widget->model instanceof $stormedModelClass) {
                    $placement = $stormedModelDef['placement'] ?? 'fields';
                    $prefix = $stormedModelDef['prefix'] ?? 'seo_options';
                    $excludeFields = $stormedModelDef['excludeFields'] ?? [];

                    $fields = $stormedManager->getSeoFieldsDefs($excludeFields);
                    $fields = $stormedManager->addPrefix($fields, $prefix);

                    if ($placement === 'fields') {
                        $widget->fields = array_replace($widget->fields ?? [], $fields);
                    } elseif ($placement === 'tabs') {
                        $widget->tabs['fields'] = array_replace($widget->tabs['fields'] ?? [], $fields);
                    } elseif ($placement === 'secondaryTabs') {
                        $widget->secondaryTabs['fields'] = array_replace($widget->secondaryTabs['fields'] ?? [], $fields);
                    }
                    break;
                }
            }
        });
    }
}
