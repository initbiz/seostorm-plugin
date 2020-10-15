<?php

namespace Arcane\Seo\Components;

use App;
use Config;
use Cms\Components\ViewBag;
use Cms\Classes\ComponentBase;
use Arcane\Seo\Models\Settings;
use System\Classes\MediaLibrary;

class Seo extends ComponentBase
{
    public $settings;

    public $disable_schema;

    public $viewBagProperties;

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
    }

    public function getTitle()
    {
        $title = $this->getPropertyTranslated('meta_title') ?? $this->viewBagProperties['title'];

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
        $description = Settings::instance()->site_description;
        if (!$description) {
            $description = $this->viewBagProperties['description'];
        }
        if (!$description) {
            $description = $this->getPropertyTranslated('meta_description');
        }
        return $description;
    }

    public function getOgTitle()
    {
        return $this->getPropertyTranslated('og_title') ?? $this->getTitle();
    }

    public function getOgDescription()
    {
        return $this->getPropertyTranslated('og_description') ?? $this->getDescription();
    }

    public function getOgImage()
    {
        if ($this->getPropertyTranslated('og_image')) {
            $ogImage = MediaLibrary::instance()->getPathUrl($this->getPropertyTranslated('og_image'));
        }
        return $ogImage ?? $this->getSiteImageFromSettings();
    }

    public function getOgVideo()
    {
        return $this->viewBagProperties['og_video'] ?? null;
    }

    public function getOgType()
    {
        return $this->viewBagProperties['og_type'] ?? 'website';;
    }

    public function getSiteImageFromSettings()
    {
        $siteImage = null;
        if (Settings::instance()->site_image_from === 'media' && Settings::instance()->site_image) {
            $siteImage = MediaLibrary::instance()->getPathUrl(Settings::instance()->site_image);
        }

        if (Settings::instance()->site_image_from === "fileupload") {
            $siteImage = Settings::instance()->site_image_fileupload()->getSimpleValue();
        }

        if (Settings::instance()->site_image_from === "url") {
            $siteImage = Settings::instance()->site_image_url;
        }
        return $siteImage;
    }

    public function getPropertyTranslated(string $viewBagPropertie)
    {
        $locale = App::getLocale();
        $property= null;

        if (isset($this->viewBagProperties[$viewBagPropertie])) {
            $property = $this->viewBagProperties[$viewBagPropertie];
        }

        if (isset($this->viewBagProperties['Locale' . $viewBagPropertie . '[' . $locale . ']'])) {
            $property = $this->viewBagProperties['Locale' . $viewBagPropertie . '['. $locale . ']'];
        }
        return $property;
    }
}
