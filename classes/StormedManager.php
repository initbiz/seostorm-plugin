<?php

namespace Initbiz\SeoStorm\Classes;

use Yaml;
use System\Classes\PluginManager;
use October\Rain\Support\Singleton;
use Initbiz\SeoStorm\Models\Settings;

/**
 * Class which handles stormed models and their definitions
 */
class StormedManager extends Singleton
{
    /**
     * Array storing stormed models
     *
     * @var array
     */
    protected $stormedModels;

    /**
     * Local cache var
     *
     * @var array
     */
    protected $fieldsDefs;

    /**
     * Getter for stormed models
     *
     * @return array
     */
    public function getStormedModels()
    {
        if (!empty($this->stormedModels)) {
            return $this->stormedModels;
        }

        $methodName = 'registerStormedModels';

        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();

        $stormedModels = [];

        foreach ($plugins as $plugin) {
            if (method_exists($plugin, $methodName)) {
                $methodResult = $plugin->$methodName() ?? [];
                $stormedModels = array_merge($stormedModels, $methodResult);
            }
        }

        $this->stormedModels = $stormedModels;

        return $stormedModels;
    }

    /**
     * Add stormed model dynamically
     *
     * @param string $class
     * @param array $modelDef according to docs
     * @return void
     */
    public function addStormedModel($class, $modelDef = [])
    {
        $this->stormedModels[$class] = $modelDef;
    }

    /**
     * Get SEO fields definitions
     *
     * @param string $prefix
     * @param array $excludeFields
     * @return array
     */
    public function getSeoFieldsDefs(array $excludeFields = [])
    {
        if (!empty($this->fieldsDefs)) {
            return $this->fieldsDefs;
        }

        $fieldsDefinitions = [];

        $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/metafields.yaml'));
        $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);

        if (Settings::get('enable_og')) {
            $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/ogfields.yaml'));
            $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);
        }

        if (Settings::get('enable_sitemap')) {
            $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/sitemapfields.yaml'));
            $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);
        }

        // Inverted excluding
        if (in_array('*', $excludeFields)) {
            $newExcludeFields = [];
            foreach ($fieldsDefinitions as $key => $fieldDef) {
                if (!in_array($key, $excludeFields)) {
                    $newExcludeFields[] = $key;
                }
            }
            $excludeFields = $newExcludeFields;
        }

        // Exclude fields
        $readyFieldsDefs = [];
        foreach ($fieldsDefinitions as $key => $fieldDef) {
            if (!in_array($key, $excludeFields)) {
                $readyFieldsDefs[$key] = $fieldDef;
            }
        }

        $this->fieldsDefs = $readyFieldsDefs;

        return $readyFieldsDefs;
    }

    public function seoFieldsToTranslate()
    {
        $toTrans = [];
        $fieldsDefinitions = $this->getSeoFieldsDefs();

        foreach ($this->addPrefix($fieldsDefinitions) as $fieldKey => $fieldValue) {
            if (isset($fieldValue['trans']) && $fieldValue['trans'] === true) {
                $toTrans[] = $fieldKey;
            }
        }

        return $toTrans;
    }

    public function getSeoFieldsDefsForEditor()
    {
        $fields = $this->addPrefix($this->getSeoFieldsDefs(), 'seo_options', '%s_%s');

        $editorFields = [];
        foreach ($fields as $key => $fieldDef) {
            $type = $fieldDef['type'] ?? 'string';

            switch ($type) {
                case 'text':
                case 'textarea':
                case 'datepicker':
                    $type = 'string';
                    break;
                case 'balloon-selector':
                    $type = 'dropdown';
                    break;
            }

            $field = [
                'property' => camel_case($key),
                'type' => $type,
                'title' => $fieldDef['label'] ?? $fieldDef['title'] ?? '',
                'tab' => $fieldDef['tab'] ?? '',
                'placeholder' => $fieldDef['placeholder'] ?? '',
                'default' => $fieldDef['default'] ?? '',
                'description' => $fieldDef['comment'] ?? $fieldDef['commentAbove'] ?? '',
                'options' => $fieldDef['options'] ?? [],
            ];

            $newField = [];

            foreach ($field as $key => $property) {
                if (!empty($property)) {
                    $newField[$key] = $property;
                }
            }

            $editorFields[] = $newField;
        }

        return $editorFields;
    }

    // Helpers

    /**
     * Walk on the array of fields and add prefix
     *
     * @param array $fieldsDefinitions
     * @param string $prefix
     * @param string $format
     * @return array
     */
    public function addPrefix($fieldsDefinitions, $prefix = 'seo_options', $format = '%s[%s]')
    {
        $readyFieldsDefs = [];
        foreach ($fieldsDefinitions as $key => $fieldDef) {
            $newKey = sprintf($format, $prefix, $key);
            // Make javascript trigger work with the prefixed fields
            if (isset($fieldDef['trigger'])) {
                $fieldDef['trigger']['field'] = sprintf($format, $prefix, $fieldDef['trigger']['field']);
            }
            $readyFieldsDefs[$newKey] = $fieldDef;
        }

        return $readyFieldsDefs;
    }
}
