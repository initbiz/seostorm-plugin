<?php

namespace Initbiz\SeoStorm\EventHandlers;

class BackenStormedHandler
{
    public function subscribe($event)
    {
        $this->addJsToBackend($event);
    }

    protected function addJsToBackend($event)
    {
        $event->listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
            $controller->addJs('/plugins/initbiz/seostorm/assets/initbiz.seostorm.js');
        });
    }
}
