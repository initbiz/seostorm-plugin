<?php namespace Arcane\Seo\Classes;

use Spatie\SchemaOrg\Schema;
use Carbon\Carbon;
use Carbon\CarbonInterval;

use October\Rain\Parse\Twig;
use Arcane\Seo\Classes\Helper;

trait SchemaComponentTrait
{
    public $commonProperties = [];

    public $dateProperties = [
        'dateModified' => [
            'title' => 'Date modified',
            'description' => 'The date and time the article was most recently modified, in ISO 8601 format.',
            'group' => 'Properties',
        ],
        'datePublished' => [
            'title' => 'Date published',
            'description' => 'The date and time the article was first published, in ISO 8601 format.',
            'group' => 'Properties',
        ],

    ];

    public $authorProperties = [
        
        'author_type' => [
            'title' => 'Type',
            'description' => 'Type of author: Organization or Person',
            'type' => 'dropdown',
            'options' => ['Organization'=>'Organization', 'Person'=> 'Person'],
            'group' => 'Author'
        ],
        'author_name' => [
            'title' => 'Author name',
            'description' => 'Name of the author',
            'group' => 'Author'
        ],
    ];

    public $publisherProperties = [
        
        'publisher_type' => [
            'title' => 'Type',
            'description' => 'Type of publisher: Organization or Person',
            'type' => 'dropdown',
            'options' => ['Organization'=>'Organization', 'Person'=> 'Person'],
            'group' => 'Publisher'
        ],

        'publisher_name' => [
            'title' => 'Publisher name',
            'description' => 'Name of the publisher',
            'group' => 'Publisher'
        ],

        'publisher_logo' => [
            'title' => 'Publisher logo',
            'description' => 'Logo of the publisher',
            'group' => 'Publisher'
        ],
    ];

    public $script = "";
    public function onRender() {
        return $this->script;
    }

    public function setScript($script) {
        $this->script = $script;
        $this->setSchema($script);
    }
    
    public function getSchemas() {
        $page = $this->page;
        // dd($page, get_class($page));
        if (is_array($page->viewBag))
            return $page->apiBag['staticPage']->viewBag['schemas'] ?? [];
        else
            return $page->viewBag->property('schemas') ?: [];
    }
    
    public function setSchema($script) {
        $schemas = $this->getSchemas();
        $schemas[$this->alias] = $this;
        $page = $this->page;
        // dd($this);
        if (is_array($page->viewBag))
            $this->page->page->viewBag['schemas'] = $schemas;
        else
            $page->viewBag->setProperty('schemas', $schemas);
    }
    
    public function getAuthor() {
        
        $type = $this->property('author_type');
        if (! $type) return;

        return Schema::$type()
            ->name($this->property('author_name'))
            ;
    }

    public function getPublisher() {

        $type = $this->property('publisher_type');
        if (! $type) return;
        return Schema::$type() 
            ->name($this->property('publisher_name'))
            ->logo(
                Schema::ImageObject()
                    ->url($this->property('publisher_logo'))
            )
            ;
    }
    
    

    public function getMainEntityOfPage() {
        return url( $this->page->canonical_url ?: $this->page->apiBag['staticPage']->viewBag['canonical_url'] ?? \Request::url() );
    }
    
   
   
    public function twig($prop) {
        $value = $this->property($prop) ?: "null";
        $enableTwig = $this->property('enable_twig');
        return Helper::parseIfTwigSyntax($enableTwig ? "{{ $value }}" : $value, $this->page->controller->vars);
    }
    
    
}
