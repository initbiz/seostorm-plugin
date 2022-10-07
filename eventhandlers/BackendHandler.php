<?php

namespace Initbiz\SeoStorm\EventHandlers;

use App;
use Site;
use Cms\Classes\Page;
use System\Classes\PluginManager;
use RainLab\Translate\Classes\Locale;
use Initbiz\SeoStorm\Classes\StormedManager;

class BackendHandler
{
    public function subscribe($event)
    {
        if (App::runningInBackend()) {
            $this->addJsToBackend($event);
            $this->extendEditor($event);
            $this->extendFormWidgets($event);
            $this->addFillableToPageModel($event);
        }
    }

    protected function addJsToBackend($event)
    {
        $event->listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
            $controller->addJs('/plugins/initbiz/seostorm/assets/initbiz.seostorm.js');
        });
    }

    /**
     * This applies to the new version of octobercms
     * Adds a button in the page toolbar editor
     */
    protected function extendEditor($event)
    {
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

                // Handle translated fields

                $pluginManager = PluginManager::instance();
                if (!$pluginManager->exists('RainLab.Translate')) {
                    return;
                }

                // RainLab.Translate v.1 compatibility
                if (class_exists(\RainLab\Translate\Models\Locale::class)) {
                    $localeClass = \RainLab\Translate\Models\Locale::class;
                } else {
                    $localeClass = Locale::class;
                }

                if ($localeClass::isAvailable()) {
                    $locales = $localeClass::listAvailable();
                    $defaultLocale = $localeClass::getDefault()->code ?? null;

                    $properties = $this->createLocaleProperties($locales, $defaultLocale, $stormedManager);
                    $dataHolder->buttons[] = $this->createLocaleButtonConfig($properties);
                }
            }
        });
    }

    /**
     * Add SEO fields to form widgets of the Stormed models in the backend
     *
     * @param Event $event
     * @return void
     */
    protected function extendFormWidgets($event)
    {
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

    protected function addFillableToPageModel($event)
    {
        Page::extend(function ($model) {
            $stormedManager = StormedManager::instance();

            $fields = $stormedManager->getSeoFieldsDefs();
            $fields = $stormedManager->addPrefix($fields, 'seo_options', '%s_%s');

            $model->addFillable(array_keys($fields));
        });
    }

    private function createLocaleButtonConfig(array $properties): array
    {
        return [
            'button' => 'initbiz.seostorm::lang.editor.translate',
            'icon' => 'octo-icon-globe',
            'popupTitle' => 'initbiz.seostorm::lang.editor.translate',
            'useViewBag' => true,
            'properties' => $properties
        ];
    }

    private function createLocaleProperties(
        iterable $locales,
        ?string $defaultLocale,
        StormedManager $stormedManager
    ): array {
        $properties = [];

        foreach ($locales as $locale => $label) {
            if ($locale === $defaultLocale) {
                continue;
            }

            $properties = array_merge(
                $stormedManager->getTranslateSeoFieldsDefsForEditor($label, $locale),
                $properties
            );
        }

        return $properties;
    }
}
