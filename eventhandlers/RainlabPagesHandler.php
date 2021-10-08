<?php

namespace Initbiz\SeoStorm\EventHandlers;

use RainLab\Pages\Classes\Page;
use Initbiz\SeoStorm\Classes\StormedManager;

class RainlabPagesHandler
{
    public function subscribe($event)
    {
        $this->addSeoFields($event);
    }

    protected function addSeoFields($event)
    {
        $event->listen('backend.form.extendFieldsBefore', function ($widget) {
            $stormedManager = StormedManager::instance();
            if ($widget->isNested === false && $widget->model instanceof Page) {
                $excludeFields = [
                    'model_class',
                    'model_scope',
                    'model_params',
                ];

                $fields = $stormedManager->getSeoFieldsDefs($excludeFields);
                $fields = $stormedManager->addPrefix($fields, 'viewBag');

                $widget->tabs['fields'] = array_replace($widget->tabs['fields'] ?? [], $fields);
            }
        });
    }
}
