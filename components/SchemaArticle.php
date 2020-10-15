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
            'name'        => 'arcane.seo::lang.components.schema_article.name',
            'description' => 'arcane.seo::lang.components.schema_article.name'
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
            'title' => 'arcane.seo::lang.components.schema_article.properties.headline.title',
            'description' => 'arcane.seo::lang.components.schema_article.properties.headline.description',
            'group' => 'Properties',
        ],
        'image' => [
            'title' => 'arcane.seo::lang.components.schema_article.properties.headline.title',
            'description' => 'arcane.seo::lang.components.schema_article.properties.headline.description',
            'group' => 'Properties'
        ],
    ];
}
