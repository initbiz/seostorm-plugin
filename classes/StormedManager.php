<?php

namespace Initbiz\SeoStorm\Classes;

use App;
use Yaml;
use Backend\Facades\BackendAuth;
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
     * Initialize this singleton.
     */
    protected function init()
    {
        if ($this->stormedModels) {
            return $this->stormedModels;
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

        $this->stormedModels = $result;
    }

    /**
     * Getter for stormed models
     *
     * @return array
     */
    public function getStormedModels()
    {
        return $this->stormedModels;
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
    public function getSeoFieldsDefinitions(string $prefix = 'seo_options', array $excludeFields = [])
    {
        // TODO: this method is heavy, difficult to understand and definitely to refactor
        $runningInFrontend = true;
        if (App::runningInBackend()) {
            $runningInFrontend = false;
            $user = BackendAuth::getUser();
        }

        $fieldsDefinitions = [];

        if ($runningInFrontend || $user->hasAccess('initbiz.seostorm.meta')) {
            $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/metafields.yaml'));
            $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);
        }

        if (Settings::get('enable_og')) {
            if ($runningInFrontend || $user->hasAccess('initbiz.seostorm.og')) {
                $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/ogfields.yaml'));
                $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);
            }
        }

        if (Settings::get('enable_sitemap')) {
            if ($runningInFrontend || $user->hasAccess('initbiz.seostorm.sitemap')) {
                $fields = Yaml::parseFile(plugins_path('initbiz/seostorm/config/sitemapfields.yaml'));
                $fieldsDefinitions = array_merge($fieldsDefinitions, $fields);
            }
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

        $readyFieldsDefs = [];
        foreach ($fieldsDefinitions as $key => $fieldDef) {
            if (!in_array($key, $excludeFields)) {
                $newKey = $prefix . '[' . $key . ']';
                // Make javascript trigger work with the prefixed fields
                if (isset($fieldDef['trigger'])) {
                    $fieldDef['trigger']['field'] = $prefix . '[' . $fieldDef['trigger']['field'] . ']';
                }
                $readyFieldsDefs[$newKey] = $fieldDef;
            }
        }

        return $readyFieldsDefs;
    }

    public function seoFieldsToTranslate()
    {
        $toTrans = [];
        foreach ($this->getSeoFieldsDefinitions() as $fieldKey => $fieldValue) {
            if (isset($fieldValue['trans']) && $fieldValue['trans'] === true) {
                $toTrans[] = $fieldKey;
            }
        }
        return $toTrans;
    }
}
