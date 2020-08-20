<?php

namespace Arcane\Seo\Components;

use Spatie\SchemaOrg\Schema;
use Cms\Classes\ComponentBase;

class SchemaArticle extends ComponentBase
{
    use \Arcane\Seo\Classes\SchemaComponentTrait;

    public function componentDetails()
    {
        return [
            'name'        => 'Article (schema.org)',
            'description' => 'Interts a schema.org article in JSON-LD'
        ];
    }

    public function onRun()
    {
        $this->setScript(Schema::Article()
            ->headline($this->property('headline'))
            ->image($this->property('image'))
            ->datePublished($this->property('datePublished'))
            ->dateModified($this->property('dateModified'))
            ->publisher($this->getPublisher())
            ->author($this->getAuthor())
            ->setProperty('mainEntityOfPage', $this->getMainEntityOfPage())
            ->toScript());
    }

    public function defineProperties()
    {
        return array_merge(
            $this->commonProperties,
            $this->myProperties,
            $this->publisherProperties,
            $this->authorProperties,
            $this->dateProperties
        );
    }

    public $myProperties =  [
        'headline' => [
            'title' => 'Headline',
            'description' => '',
            'group' => 'Properties',
        ],
        'image' => [
            'title' => 'Image',
            'description' => '',
            'group' => 'Properties'
        ],
    ];
}
