<?php

namespace Initbiz\SeoStorm\Components;

use Initbiz\SeoStorm\Components\Seo;

class Schema extends Seo
{
    public $publisher;

    public function componentDetails()
    {
        return [
            'name'        => 'initbiz.seostorm::lang.components.schema.name',
            'description' => 'initbiz.seostorm::lang.components.schema.description'
        ];
    }

    public function onRun()
    {
        $this->getPublisher();
    }

    public function getPublisher()
    {
        $settings = $this->getSettings();
        $this->publisher = [
            'type' => $settings['publisher_type'],
            'name' => $settings['publisher_name'],
            'url' => $settings['publisher_url'],
            'logo_url' => $settings['publisher_logo_url'],
            'same_as' => $settings['publisher_same_as'],
        ];
    }
}
