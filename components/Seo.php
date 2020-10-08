<?php

namespace Arcane\Seo\Components;

use Cms\Components\ViewBag;
use Cms\Classes\ComponentBase;
use Arcane\Seo\Models\Settings;

class Seo extends ComponentBase
{
    public $settings;

    public $disable_schema;

    public $viewBag;

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

    public function onRun()
    {
        $this->settings = Settings::instance();

        if (!$this->page['viewBag']) {
            $this->page['viewBag'] = new ViewBag();
        }

        if ($this->page->page->hasComponent('blogPost'))
        {
            $post = $this->page['post'];
            $this->page['viewBag']->setProperties(array_merge(
                $this->page["viewBag"]->getProperties(),
                $post->attributes,
                $post->arcane_seo_options ?: []
            ));
        } elseif (isset($this->page->apiBag['staticPage'])) {
            $this->page['viewBag'] = $this->page->controller->vars['page']->viewBag;
        } else {
            $this->page['viewBag']->setProperties(array_merge($this->page['viewBag']->getProperties(), $this->page->settings));
        }

        $viewBag = $this->page["viewBag"];
        $this->openGraph = [
            'title' => $viewBag['og_title'] ?? $viewBag['meta_title'],
        ];
        $this->viewBag = $this->page["viewBag"];
        $this->disable_schema = $this->property('disable_schema');
    }

    public function getOgTitle()
    {
        return $this->viewBag['og_title'] ?? $this->viewBag['meta_title'];
    }

    public function getOgDescription()
    {
        return $this->viewBag['og_description'] ?? $this->viewBag['meta_description'];
    }

    public function getOgImage()
    {
        $settings = Settings::instance();
        dd($settings->site_image);
        return $this->viewBag['og_image'] ?? $this->viewBag[''];
    }

    public function getOgType()
    {
        return $this->viewBag['og_type'] ?? 'website';
    }
}
