<?php

namespace Arcane\Seo\Components;

use App;
use Cms\Components\ViewBag;
use Cms\Classes\ComponentBase;
use Arcane\Seo\Models\Settings;
use System\Classes\MediaLibrary;

class Seo extends ComponentBase
{
    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public $settings;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public $disable_schema;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
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
    /**
     * Returns the title of the page taking position from settings into consideration
     *
     * @return string title of the page;
     */
    public function getTitle()
    {
        $title = $this->getPropertyTranslated('meta_title') ?? $this->viewBagProperties['title'];

        $settings = Settings::instance();
        if ($settings->site_name_position == 'prefix') {
            $title = "{$settings->site_name} {$settings->site_name_separator} {$title}";
        } elseif ($settings->site_name_position == 'suffix') {
            $title = "{$title} {$settings->site_name_separator} {$settings->site_name}";
        }

        return $title;
    }

    /**
     * Returns the deafualt description from settings otherwise if set description or meta_description in the viewBag her value
     *
     * @return string description of the page
     */
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

    /**
     * Returns og_title if set in the viewBag otherwise fall back to getTitle
     *
     * @return string social media title
     */
    public function getOgTitle()
    {
        return $this->getPropertyTranslated('og_title') ?? $this->getTitle();
    }

    /**
     * Returns og_description if is set in the ViewBag otherwise fall back to getDescription
     *
     * @return string social media description
     */
    public function getOgDescription()
    {
        return $this->getPropertyTranslated('og_description') ?? $this->getDescription();
    }

    /**
     * Returns og_image if is set in the viewBag otherwise run function getSiteImageFromSettings and return het value
     *
     * @return void
     */
    public function getOgImage()
    {
        if ($ogImage = $this->getPropertyTranslated('og_image')) {
            return MediaLibrary::instance()->getPathUrl($ogImage);
        }

        return $this->getSiteImageFromSettings();
    }

    /**
     * Returns og_video if is set in the viewBag
     *
     * @return void
     */
    public function getOgVideo()
    {
        return $this->viewBagProperties['og_video'] ?? null;
    }

    /**
     * Returns og_type if is set in the viewBag otherwise website
     *
     * @return void
     */
    public function getOgType()
    {
        return $this->viewBagProperties['og_type'] ?? 'website';
    }

    /**
     * Check 
     *
     * @return void
     */
    public function getSiteImageFromSettings()
    {
        $siteImageFrom = Settings::instance()->site_image_from;
        if ($siteImageFrom === 'media' && Settings::instance()->site_image) {
            return MediaLibrary::instance()->getPathUrl(Settings::instance()->site_image);
        }elseif ($siteImageFrom === "fileupload") {
            return Settings::instance()->site_image_fileupload()->getSimpleValue();
        }elseif ($siteImageFrom === "url") {
            return Settings::instance()->site_image_url;
        }
    }

    /**
     * Undocumented function
     *
     * @param string $viewBagProperty
     * @return void
     */
    public function getPropertyTranslated(string $viewBagProperty)
    {
        $locale = App::getLocale();

        if (isset($this->viewBagProperties['Locale' . $viewBagProperty . '[' . $locale . ']'])) {
            return $this->viewBagProperties['Locale' . $viewBagProperty . '['. $locale . ']'];
        }elseif (isset($this->viewBagProperties[$viewBagProperty])) {
            return $this->viewBagProperties[$viewBagProperty];
        }
    }
}
