<?php

namespace Initbiz\SeoStorm\Components;

use Spatie\SchemaOrg\Schema;
use Cms\Classes\ComponentBase;


class SchemaVideo extends ComponentBase
{
    use \Initbiz\SeoStorm\Classes\SchemaComponentTrait;

    public function componentDetails()
    {
        return [
            'name'        => 'initbiz.seostorm::lang.components.schema_video.name',
            'description' => 'initbiz.seostorm::lang.components.schema_video.description'
        ];
    }

    function onRun()
    {
        $this->setScript(Schema::VideoObject()
            ->name($this->property('name'))
            ->description($this->property('description'))
            ->thumbnailUrl($this->property('thumbnailUrl'))
            ->uploadDate($this->property('uploadDate'))
            ->duration($this->property('duration'))
            ->interactionCount($this->property('interactionCount'))
            ->toScript());
    }

    public function defineProperties()
    {
        return array_merge($this->commonProperties, $this->myProperties);
    }


    public  $myProperties = [
        'name' => [
            'title' => 'initbiz.seostorm::lang.components.schema_video.properties.name.title',
            'description' => 'initbiz.seostorm::lang.components.schema_video.properties.name.description',
            'group' => 'initbiz.seostorm::lang.components.group.properties',
            'required'
        ],
        'description' => [
            'title' => 'initbiz.seostorm::lang.components.schema_video.properties.description.title',
            'description' => 'initbiz.seostorm::lang.components.schema_video.properties.description.description',
            'group' => 'initbiz.seostorm::lang.components.group.properties',
        ],
        'thumbnailUrl' => [
            'title' => 'initbiz.seostorm::lang.components.schema_video.properties.thumbnail_url.title',
            'description' => 'initbiz.seostorm::lang.components.schema_video.properties.thumbnail_url.description',
            'group' => 'initbiz.seostorm::lang.components.group.properties',
        ],
        'uploadDate' => [
            'title' => 'initbiz.seostorm::lang.components.schema_video.properties.upload_date.title',
            'description' => 'initbiz.seostorm::lang.components.schema_video.properties.upload_date.description',
            'group' => 'initbiz.seostorm::lang.components.group.properties',
        ],
        'duration' => [
            'title' => 'initbiz.seostorm::lang.components.schema_video.properties.duration.title',
            'description' => 'initbiz.seostorm::lang.components.schema_video.properties.duration.description',
            'group' => 'initbiz.seostorm::lang.components.group.properties',
        ],
        'interactionCount' => [
            'title' => 'initbiz.seostorm::lang.components.schema_video.properties.interaction_count.title',
            'description' => 'initbiz.seostorm::lang.components.schema_video.properties.interaction_count.description',
            'group' => 'initbiz.seostorm::lang.components.group.properties',
        ],
    ];
}
