<?php

namespace Arcane\Seo\Components;

use Spatie\SchemaOrg\Schema;
use Cms\Classes\ComponentBase;


class SchemaVideo extends ComponentBase
{
    use \Arcane\Seo\Classes\SchemaComponentTrait;

    public function componentDetails()
    {
        return [
            'name'        => 'arcane.seo::lang.components.schema_video.name',
            'description' => 'arcane.seo::lang.components.schema_video.description'
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
            'title' => 'arcane.seo::lang.components.schema_video.properties.name.title',
            'description' => 'arcane.seo::lang.components.schema_video.properties.name.description',
            'group' => 'arcane.seo::lang.components.group.properties',
            'required'
        ],
        'description' => [
            'title' => 'arcane.seo::lang.components.schema_video.properties.description.title',
            'description' => 'arcane.seo::lang.components.schema_video.properties.description.description',
            'group' => 'arcane.seo::lang.components.group.properties',
        ],
        'thumbnailUrl' => [
            'title' => 'arcane.seo::lang.components.schema_video.properties.thumbnail_url.title',
            'description' => 'arcane.seo::lang.components.schema_video.properties.thumbnail_url.description',
            'group' => 'arcane.seo::lang.components.group.properties',
        ],
        'uploadDate' => [
            'title' => 'arcane.seo::lang.components.schema_video.properties.upload_date.title',
            'description' => 'arcane.seo::lang.components.schema_video.properties.upload_date.description',
            'group' => 'arcane.seo::lang.components.group.properties',
        ],
        'duration' => [
            'title' => 'arcane.seo::lang.components.schema_video.properties.duration.title',
            'description' => 'arcane.seo::lang.components.schema_video.properties.duration.description',
            'group' => 'arcane.seo::lang.components.group.properties',
        ],
        'interactionCount' => [
            'title' => 'arcane.seo::lang.components.schema_video.properties.interaction_count.title',
            'description' => 'arcane.seo::lang.components.schema_video.properties.interaction_count.description',
            'group' => 'arcane.seo::lang.components.group.properties',
        ],
    ];
}
