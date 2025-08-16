<?php

declare(strict_types=1);

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
        $fieldsDefs = $this->getFieldsDefs();

        $readyFieldsDefs = $this->excludeFields($fieldsDefs, $excludeFields);

        return $readyFieldsDefs;
    }

    /**
     * Get SEO fields definitions
     *
     * @return array
     */
    public function getFieldsDefs()
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

        $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/schemafields.yaml'));
        $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);

        $this->fieldsDefs = $fieldsDefinitions;

        return $fieldsDefinitions;
    }

    /**
     * Filter exclude fields from the provided fields
     *
     * @param array $fieldsDefinitions
     * @param array $excludeFields
     * @return array
     */
    public function excludeFields($fieldsDefinitions, $excludeFields)
    {
        // Inverted excluding
        if (in_array('*', $excludeFields, true)) {
            $newExcludeFields = [];
            foreach ($fieldsDefinitions as $key => $fieldDef) {
                if (!in_array($key, $excludeFields, true)) {
                    $newExcludeFields[] = $key;
                }
            }
            $excludeFields = $newExcludeFields;
        }

        // Exclude fields
        $readyFieldsDefs = [];
        foreach ($fieldsDefinitions as $key => $fieldDef) {
            if (!in_array($key, $excludeFields, true)) {
                $readyFieldsDefs[$key] = $fieldDef;
            }
        }

        return $readyFieldsDefs;
    }

    /**
     * Return all the fields that are translatable
     *
     * @return array
     */
    public function getTranslatableSeoFieldsDefs()
    {
        $toTrans = [];
        $fieldsDefinitions = $this->getSeoFieldsDefs();

        foreach ($fieldsDefinitions as $fieldKey => $fieldValue) {
            if (isset($fieldValue['trans']) && $fieldValue['trans'] === true) {
                $toTrans[$fieldKey] = $fieldValue;
            }
        }

        return $toTrans;
    }

    public function getSeoFieldsDefsForEditor()
    {
        $fields = $this->addPrefix($this->getSeoFieldsDefs(), 'seo_options', '%s_%s');

        $editorFields = [];
        foreach ($fields as $key => $fieldDef) {
            $editorFields[] = $this->makeField($key, $fieldDef);
        }

        return $editorFields;
    }

    public function getTranslateSeoFieldsDefsForEditor(string $langName, string $langCode)
    {
        $fields = $this->addPrefix($this->getTranslatableSeoFieldsDefs(), 'locale_seo_options', '%s_%s');

        $editorFields = [];
        foreach ($fields as $key => $fieldDef) {
            $newFieldKey = $key . '.' . $langCode;
            $editorFields[] = $this->makeField($newFieldKey, $fieldDef, $langName);
        }

        return $editorFields;
    }

    public function makeField($key, $fieldDef, $customTab = null)
    {
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

        $tab = $customTab ?? $fieldDef['tab'] ?? '';

        $field = [
            'property' => camel_case($key),
            'type' => $type,
            'title' => $fieldDef['label'] ?? $fieldDef['title'] ?? '',
            'tab' => $tab,
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

        return $newField;
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
