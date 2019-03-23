<?php namespace Arcane\Seo\Components;

use Cms\Classes\ComponentBase;
use Spatie\SchemaOrg\Schema;


class SchemaVideo extends ComponentBase
{
    use \Arcane\Seo\Classes\SchemaComponentTrait;

    public function componentDetails()
    {
        return [
            'name'        => 'Video (schema.org)',
            'description' => 'Inserts an schema.org VideoObject'
        ];
    }

    function onRun() {
        $this->setScript( Schema::VideoObject()
            ->name($this->property('name'))
            ->description($this->property('description'))
            ->thumbnailUrl($this->property('thumbnailUrl'))
            ->uploadDate($this->property('uploadDate'))
            ->duration($this->property('duration'))
            ->interactionCount($this->property('interactionCount'))
            ->toScript()
        )
        ;
    }

    public function defineProperties()
    {
        return array_merge($this->commonProperties, $this->myProperties);
    }


    public  $myProperties = [
        'name' => [
            'title' => 'Name',
            'description' => 'Name of the video ',
            'group' => 'Properties',
            'required' 
        ],
        'description' => [
            'title' => 'Description',
            'description' => 'Description of the video ',
            'group' => 'Properties',
        ],
        'thumbnailUrl' => [
            'title' => 'Thumbnail URL',
            'description' => 'Thumbnail of the video ',
            'group' => 'Properties',
        ],
        'uploadDate' => [
            'title' => 'Upload Date',
            'description' => 'Upload date of the video ',
            'group' => 'Properties',
        ],
        'duration' => [
            'title' => 'Duration',
            'description' => 'Duration of the video ',
            'group' => 'Properties',
        ],
        'interactionCount' => [
            'title' => 'Interaction count',
            'description' => 'Number of times the video has been viewed ',
            'group' => 'Properties',
        ],
    ];
}
