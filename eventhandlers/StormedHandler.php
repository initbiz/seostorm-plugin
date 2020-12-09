<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Yaml;
use BackendAuth;
use System\Classes\PluginManager;
use Initbiz\SeoStorm\Models\SeoOptions;

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
        $this->extendFormWidgets($event);
    }

    protected function extendModels($event)
    {
        foreach ($this->getStormedModels() as $stormedModelClass => $stormedModelDef) {
            if (!class_exists($stormedModelClass)) {
                continue;
            }

            $stormedModelClass::extend(function ($model) {
                if (!$model->isClassExtendedWith('Initbiz.SeoStorm.Behaviors.SeoStormed')) {
                    $model->extendClassWith('Initbiz.SeoStorm.Behaviors.SeoStormed');
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

                if (PluginManager::instance()->hasPlugin('RainLab.Translate')) {
                    if (!$model->propertyExists('translatable')) {
                        $model->addDynamicProperty('translatable', []);
                    }
                    $model->translatable = array_merge($model->translatable, $this->seoFieldsToTranslate());
                }

            });

            // Define reverse of the relation in the SeoOptions model
            SeoOptions::extend(function ($model) use ($stormedModelClass) {
                $model->morphTo['stormed_models'][] = [
                    $stormedModelClass,
                    'name' => 'stormed',
                    'table' => 'initbiz_seostorm_seo_options',
                ];
            });
        }
    }

    protected function extendFormWidgets($event)
    {
        $event->listen('backend.form.extendFieldsBefore', function ($widget) {
            foreach ($this->getStormedModels() as $stormedModelClass => $stormedModelDef) {
                if ($widget->model instanceof $stormedModelClass) {
                    $placement = $stormedModelDef['placement'] ?? 'fields';
                    $prefix = $stormedModelDef['prefix'] ?? 'seo_options';
                    $excludeFields = $stormedModelDef['excludeFields'] ?? [];

                    $fields = $this->getSeoFieldsDefinitions($prefix, $excludeFields);

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

    // helpers

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
                $methodResult = $plugin->$methodName() ?? [];
                $result = array_merge($result, $methodResult);
            }
        }

        $this->modelClasses = $result;
        return $this->modelClasses;
    }

    protected function seoFieldsToTranslate()
    {
        $toTrans = [];
        foreach ($this->getSeoFieldsDefinitions() as $fieldKey => $fieldValue) {
            if (isset($fieldValue['trans']) && $fieldValue['trans'] === true) {
                $toTrans[] = $fieldKey;
            }
        }
        return $toTrans;
    }

    protected function getSeoFieldsDefinitions(string $prefix = 'seo_options', array $excludeFields = [])
    {
        $user = BackendAuth::getUser();

        $fieldsDefinitions = [];

        if ($user->hasAccess("initbiz.seostorm.meta")) {
            $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/metafields.yaml'));
            $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);
        }

        if ($user->hasAccess("initbiz.seostorm.og")) {
            $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/ogfields.yaml'));
            $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);
        }

        if ($user->hasAccess("initbiz.seostorm.sitemap")) {
            $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/sitemapfields.yaml'));
            $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);
        }

        if ($user->hasAccess("initbiz.seostorm.schema")) {
            $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/schemafields.yaml'));
            $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);
        }

        $prefixedFieldsDefinitions = [];
        foreach ($fieldsDefinitions as $key => $fieldDef) {
            if (!in_array($key, $excludeFields)) {
                $newKey = $prefix . "[" . $key . "]";
                $prefixedFieldsDefinitions[$newKey] = $fieldDef;
            }
        }

        return $prefixedFieldsDefinitions;
    }
}
