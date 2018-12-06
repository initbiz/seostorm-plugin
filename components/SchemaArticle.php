<?php namespace Arcane\Seo\Components;

use Cms\Classes\ComponentBase;
use Spatie\SchemaOrg\Schema;
use October\Rain\Parse\Twig;

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

    public function onRun() {
        $this->setScript(Schema::Article()
            ->headline($this->twigParse('headline'))
            ->image($this->twigParse('image'))
            ->datePublished($this->asDate('datePublished'))
            ->dateModified($this->asDate('dateModified'))
            ->publisher($this->getPublisher())
            ->author($this->getAuthor())
            ->setProperty('mainEntityOfPage', $this->getMainEntityOfPage())
            ->toScript()
        )
        ;
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
