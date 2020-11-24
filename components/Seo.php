<?php

namespace Initbiz\SeoStorm\Components;

use App;
use Cms\Components\ViewBag;
use Cms\Classes\ComponentBase;
use System\Classes\MediaLibrary;
use Initbiz\SeoStorm\Models\Settings;

class Seo extends ComponentBase
{
    /**
     * Plugin settings
     *
     * @var Collection
     */
    public $settings;

    /**
     * Is schema disabled
     *
     * @var Boolean
     */
    public $disable_schema;

    /**
     * Current viewBag set in the current pages
     *
     * @var Array
     */
    public $viewBagProperties;

    public function componentDetails()
    {
        return [
            'name'        => 'initbiz.seostorm::lang.components.seo.name',
            'description' => 'initbiz.seostorm::lang.components.seo.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'disable_schema' => [
                'title' => 'initbiz.seostorm::lang.components.seo.properties.disable_schema.title',
                'description' => 'initbiz.seostorm::lang.components.seo.properties.disable_schema.description',
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
            //TODO fix if in the page don't have post
            if ($post = $this->page['post']) {
                $properties = array_merge(
                    $this->page["viewBag"]->getProperties(),
                    $post->attributes,
                    $post->seo_options ?: []
                );
                $this->viewBagProperties = $properties;
                $this->page['viewBag']->setProperties($properties);
            }
        } elseif (isset($this->page->apiBag['staticPage'])) {
            $this->viewBagProperties = $this->page['viewBag'] = array_merge(
                $this->page->apiBag['staticPage']->viewBag,
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
        $title = $this->getPropertyTranslated('meta_title') ?? $this->viewBagProperties['title'] ?? null;

        $settings = Settings::instance();
        if ($settings->site_name_position == 'prefix') {
            $title = "{$settings->site_name} {$settings->site_name_separator} {$title}";
        } elseif ($settings->site_name_position == 'suffix') {
            $title = "{$title} {$settings->site_name_separator} {$settings->site_name}";
        }

        return $title;
    }

    /**
     * Returns the description set in the viewBag as a meta_description
     * or description, otherwise returns the default value from the settings
     *
     * @return string page description
     */
    public function getDescription()
    {
        $description = $this->getPropertyTranslated('meta_description');

        if (!$description) {
            $description = $this->viewBagProperties['description'] ?? null;
        }

        if (!$description) {
            $description = Settings::instance()->site_description;
        }

        return $description;
    }

    /**
     * Returns og_title if set in the viewBag
     * otherwise fallback to getTitle
     *
     * @return string social media title
     */
    public function getOgTitle()
    {
        return $this->getPropertyTranslated('og_title') ?? $this->getTitle();
    }

    /**
     * Returns og_description if is set in the ViewBag
     * otherwise fallback to getDescription
     *
     * @return string social media description
     */
    public function getOgDescription()
    {
        return $this->getPropertyTranslated('og_description') ?? $this->getDescription();
    }

    /**
     * Returns og_image if set in the viewBag
     * otherwise fallback to getSiteImageFromSettings()
     *
     * @return string
     */
    public function getOgImage()
    {
        if ($ogImage = $this->getPropertyTranslated('og_image')) {
            return MediaLibrary::instance()->getPathUrl($ogImage);
        }

        return $this->getSiteImageFromSettings();
    }

    /**
     * Returns og_video if set in the viewBag
     *
     * @return string
     */
    public function getOgVideo()
    {
        return $this->viewBagProperties['og_video'] ?? null;
    }

    /**
     * Returns og_type if set in the viewBag
     * otherwise returns string 'website'
     *
     * @return string default 'website'
     */
    public function getOgType()
    {
        return $this->viewBagProperties['og_type'] ?? 'website';
    }

    /**
     * Returns the URL of the site image
     *
     * @return string site image url
     */
    public function getSiteImageFromSettings()
    {
        $siteImageFrom = Settings::instance()->site_image_from;
        if ($siteImageFrom === 'media' && Settings::instance()->site_image) {
            return MediaLibrary::instance()->getPathUrl(Settings::instance()->site_image);
        } elseif ($siteImageFrom === "fileupload") {
            return Settings::instance()->site_image_fileupload()->getSimpleValue();
        } elseif ($siteImageFrom === "url") {
            return Settings::instance()->site_image_url;
        }
    }

    /**
     * Returns the property from the viewBag
     * taking translated version into consideration
     *
     * @param string $viewBagProperty
     * @return string|null
     */
    public function getPropertyTranslated(string $viewBagProperty)
    {
        $locale = App::getLocale();
        $localizedKey = 'Locale' . $viewBagProperty . '['. $locale . ']';
        return $this->viewBagProperties[$localizedKey] ?? $this->viewBagProperties[$viewBagProperty] ?? null;
    }
}
