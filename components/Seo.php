<?php

namespace Arcane\Seo\Components;

use Cms\Components\ViewBag;
use Cms\Classes\ComponentBase;
use Arcane\Seo\Models\Settings;

class Seo extends ComponentBase
{
    public $settings;

    public $disable_schema;

    // setup the viewBag for the component
    public function onRender()
    {
        $this->settings = Settings::instance();

        $thisPage = $this->page->page;

        if (!$this->page['viewBag']) {
            $this->page['viewBag'] = new ViewBag();
        }

        if ($this->page->page->hasComponent('blogPost')) // blog post
        {
            $post = $this->page['post'];
            $this->page['viewBag']->setProperties(array_merge(
                $this->page["viewBag"]->getProperties(),
                $post->attributes,
                $post->arcane_seo_options ?: [] // quickfix avoid error when plugin just installed
            ));
        } elseif (isset($this->page->apiBag['staticPage'])) { // static page
            $this->page['viewBag'] =  $this->page->controller->vars['page']->viewBag;
        } else { // cms page
            $this->page['viewBag']->setProperties(array_merge($this->page['viewBag']->getProperties(), $this->page->settings));
        }

        $this->disable_schema = $this->property('disable_schema');
    }

    public function componentDetails()
    {
        return [
            'name'        => 'arcane.seo::lang.components.seo.name',
            'description' => 'arcane.seo::lang.components.seo.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'disable_schema' => [
                'title' => 'arcane.seo::lang.components.seo.properties.disable_schema.title',
                'description' => 'arcane.seo::lang.components.seo.properties.disable_schema.description',
                'type' => 'checkbox'
            ]
        ];
    }
}
