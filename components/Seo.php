<?php

namespace Arcane\Seo\Components;

use App;
use Config;
use Cms\Components\ViewBag;
use Cms\Classes\ComponentBase;
use Arcane\Seo\Models\Settings;

class Seo extends ComponentBase
{
    public $settings;

    public $disable_schema;

    public $viewBagProperties;

    public $locale;

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


        if ($this->page->page->hasComponent('blogPost')) {
            $post = $this->page['post'];
            $properties = array_merge(
                $this->page["viewBag"]->getProperties(),
                $post->attributes,
                $post->arcane_seo_options ?: []
            );
            $this->viewBagProperties = $properties;
            $this->page['viewBag']->setProperties($properties);
        } elseif (isset($this->page->apiBag['staticPage'])) {
            $this->viewBagProperties = $this->page['viewBag'] = array_merge(
                $this->page->controller->vars['page']->viewBag,
                $this->page->attributes
            );
        } else {
            $properties = array_merge(
                $this->page['viewBag']->getProperties(), $this->page->settings
            );

            $this->viewBagProperties = $properties;
            $this->page['viewBag']->setProperties($properties);
        }
        $this->disable_schema = $this->property('disable_schema');
        $this->locale = App::getLocale();
        $this->siteImage();
    }

    public function getTitle()
    {
        $title = $this->viewBagProperties['title'];
        if (isset($this->viewBagProperties['meta_title'])) {
            $title = $this->viewBagProperties['meta_title'];
        }

        if (isset($this->viewBagProperties['meta_title[' . $this->locale . ']'])) {
            $title = $this->viewBagProperties['meta_title[' . $this->locale . ']'];
        }

        $settings = Settings::instance();

        if ($settings->site_name_position == 'prefix') {
            $title = "{$settings->site_name} {$settings->site_name_separator} {$title}";
        } else if ($settings->site_name_position == 'suffix') {
            $title = "{$title} {$settings->site_name_separator} {$settings->site_name}";
        }

        return $title;
    }

    public function getDescription()
    {
        $description = Settings::instance()->description;
        if (isset($this->page['description'])) {
            $description = $this->page['description'];
        }

        if (isset($this->viewBagProperties['meta_description'])) {
            $description = $this->viewBagProperties['meta_description'];
        }

        if (isset($this->viewBagProperties['localeMeta_description[' . $this->locale . ']'])) {
            $description = $this->viewBagProperties['localeMeta_description[' . $this->locale . ']'];
        }
        return $description;
    }

    public function getOgTitle()
    {
        $ogTitle = $this->getTitle();
        if (isset($this->viewBagProperties['og_title'])) {
            $ogTitle = $this->viewBagProperties['og_title'];
        }

        if (isset($this->viewBagProperties['og_title[' . $this->locale . ']'])) {
            $ogTitle = $this->viewBagProperties['og_title[' . $this->locale . ']'];
        }
        return $ogTitle;
    }

    public function getOgDescription()
    {
        $ogDescription = $this->getDescription();
        if (isset($this->viewBagProperties['og_description'])) {
            $ogDescription = $this->viewBagProperties['og_description'];
        }

        if (isset($this->viewBagProperties['LocaleOg_description[' . $this->locale . ']'])) {
            $ogDescription = $this->viewBagProperties['LocaleOg_description['. $this->locale . ']'];
        }
        return $ogDescription;
    }

    public function getOgImage()
    {
        $mediaUrl = url(Config::get('cms.storage.media.path'));
        $ogImage = null;
        if ($settingsSiteImage = Settings::instance()->siteImage) {
            $ogImage = $mediaUrl . $settingsSiteImage;
        }

        if (isset($this->viewBagProperties['og_image'])) {
            $ogImage = $mediaUrl . $this->viewBagProperties['og_image'];
        }
        return $ogImage;
    }

    public function getOgVideo()
    {
        $ogVideo = null;
        if (isset($this->viewBagProperties['og_video'])) {
            $ogVideo = $this->viewBagProperties['og_video'];
        }
        return $ogVideo;
    }

    public function getOgType()
    {
        $ogType = $this->viewBagProperties['og_type'] ?? 'website';
        return $ogType;
    }

    public function siteImage()
    {
    }
}
