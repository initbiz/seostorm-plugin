<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Event;
use System\Classes\PluginManager;
use Initbiz\SeoStorm\Models\SeoOptions;

class SeoStormedModelsHandler
{
    /**
     * Array of model classes to extend
     *
     * @var array
     */
    protected $modelClasses = [];

    public function subscribe($event)
    {
        foreach ($this->getStormedModels() as $modelClass) {
            if (!class_exists($modelClass)) {
                continue;
            }
            $modelClass::extend(function ($model) {
                if (!$model->isClassExtendedWith('Initbiz.SeoStorm.Behaviors.SeoStormed')) {
                    $model->extendClassWith('Initbiz.SeoStorm.Behaviors.SeoStormed');
                }

                if (!isset($model->morphOne)) {
                    $model->addDynamicProperty('morphOne');
                }

                $model->morphOne['seostorm_options'] = [
                    SeoOptions::class,
                    'name' => 'stormed',
                    'table' => 'initbiz_seostorm_seo_options',
                ];
            });

            // Define reverse of the relation in the SeoOptions model
            SeoOptions::extend(function ($model) use ($modelClass) {
                $model->morphTo['stormed_models'][] = [
                    $modelClass,
                    'name' => 'stormed',
                    'table' => 'initbiz_seostorm_seo_options',
                ];
            });
        }
    }

    protected function getStormedModels()
    {
        if ($this->modelClasses) {
            return $this->modelClasses;
        }

        $methodName = 'registerStormedModels';

        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();

        $result = [];

        foreach ($plugins as $plugin) {
            if (method_exists($plugin, $methodName)) {
                $methodResult = $plugin->$methodName();
                $result = array_merge($result, $methodResult);
            }
        }

        $this->modelClasses = $result;
        return $this->modelClasses;
    }
}
