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

    /**
     * Where to place the seo options fields, either fields, tabs or secondaryTabs
     *
     * @var string
     */
    public $seoFieldsPlacement = 'fields';

    public function __construct($controller)
    {
        $this->controller = $controller;

        if (isset($this->controller->seoFieldsPlacement)) {
            $this->seoFieldsPlacement = $this->controller->seoFieldsPlacement;
        }

        $controller->extendFormFields(function($form, $model, $context) {
            if (!$model instanceof MyModel) {
                return;
            }

            $form->addFields([
                'my_field' => [
                    'label'   => 'My Field',
                    'comment' => 'This is a custom field I have added.',
                ],
            ]);

        });
    }

    protected function getSeoFieldsDefinitions(string $prefix = 'seo_options', array $excludeFields = [])
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
            if (!in_array($key, $excludeFields)) {
                $newKey = $prefix . "[" . $key . "]";
                $prefixedFieldsDefinitions[$newKey] = $fieldDef;
            }
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
