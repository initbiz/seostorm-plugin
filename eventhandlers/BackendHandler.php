<?php

namespace Initbiz\SeoStorm\EventHandlers;

use App;
use Cms\Classes\Page;
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
}
