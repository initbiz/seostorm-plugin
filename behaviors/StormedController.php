<?php

namespace Initbiz\SeoStorm\Behaviors;

use Yaml;
use BackendAuth;
use October\Rain\Extension\ExtensionBase;

class StormedController extends ExtensionBase
{
    /**
     * @var \Backend\Classes\Controller|FormController Reference to the back end controller.
     */
    protected $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function getSeoFieldsDefinitions(string $prefix = 'seo_options')
    {
        $user = BackendAuth::getUser();

        $fieldsDefinitions = [];

        if ($user->hasAccess("initbiz.seostorm.og")) {
            $fieldsDefinitions = array_merge($fieldsDefinitions, $this->getOgFieldsDefinitions());
        }

        if ($user->hasAccess("initbiz.seostorm.sitemap")) {
            $fieldsDefinitions = array_merge($fieldsDefinitions, $this->getSitemapFieldsDefinitions());
        }

        if ($user->hasAccess("initbiz.seostorm.meta")) {
            $fieldsDefinitions = array_merge($fieldsDefinitions, $this->getMetaFieldsDefinitions());
        }

        if ($user->hasAccess("initbiz.seostorm.schema")) {
            $fieldsDefinitions = array_merge($fieldsDefinitions, $this->getSchemaFieldsDefinitions());
        }

        $prefixedFieldsDefinitions = [];
        foreach ($fieldsDefinitions as $key => $fieldDef) {
            $newKey = $prefix . "[" . $key . "]";
            $prefixedFieldsDefinitions[$newKey] = $fieldDef;
        }

        return $prefixedFieldsDefinitions;
    }

    protected function getOgFieldsDefinitions()
    {
    }

    protected function getSitemapFieldsDefinitions()
    {
    }

    protected function getMetaFieldsDefinitions()
    {
    }

    protected function getSchemaFieldsDefinitions()
    {
    }
}
