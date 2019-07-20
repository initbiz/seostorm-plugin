<?php namespace Arcane\Seo\Components;

use Cms\Classes\ComponentBase;

use Request;
use Arcane\Seo\Models\Settings;
use Arcane\Seo\Classes\Helper;
use Cms\Classes\Theme;
use Cms\Classes\Page;
use Cms\Components\ViewBag;

class Seo extends ComponentBase
{
    public $settings;
    public $disable_schema;

    // setup the viewBag for the component
    public function onRender()
    {
        $this->settings = Settings::instance();
        $thisPage = $this->page->page;
        if (! $this->page['viewBag']) $this->page['viewBag'] = new ViewBag;

        if($this->page->page->hasComponent('blogPost')) // blog post
        {
            $post = $this->page['post'];
            $this->page['viewBag']->setProperties(array_merge(
              $this->page["viewBag"]->getProperties(), 
              $post->attributes, 
              $post->arcane_seo_options ?: [] // quickfix avoid error when plugin just installed
            ));
            
        } else if (isset($this->page->apiBag['staticPage'])) { // static page
            $this->page['viewBag'] =  $this->page->controller->vars['page']->viewBag ;

        } else { // cms page
            $this->page['viewBag']->setProperties( array_merge($this->page['viewBag']->getProperties(), $this->page->settings)) ;
        }
        $this->disable_schema = $this->property('disable_schema');
        // dd($this->page['viewBag']);

    }

    

    public function componentDetails()
    {
        return [
            'name'        => 'SEO',
            'description' => 'Renders SEO meta tags in place'
        ];
    }

    public function defineProperties()
    {
        return [
            'disable_schema' => [
                'title' => 'Disable schemas',
                'description' => 'Enable this if you do not want to output schema scripts from the seo component.',
                'type' => 'checkbox'
            ]
        ];
    }

    

}
