<?php

namespace Initbiz\SeoStorm\Components;

use App;
use Cms\Components\ViewBag;
use Cms\Classes\ComponentBase;
use Media\Classes\MediaLibrary;
use System\Classes\MediaLibrary as OldMediaLibrary;
use Initbiz\SeoStorm\Models\Settings;

class Seo extends ComponentBase
{
    /**
     * Current viewBag set in the current pages
     *
     * @var Array
     */
    public $seoAttributes;

    /**
     * Plugin settings
     *
     * @var Model
     */
    protected $settings;

    public function componentDetails()
    {
        return [
            'name'        => 'initbiz.seostorm::lang.components.seo.name',
            'description' => 'initbiz.seostorm::lang.components.seo.description'
        ];
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function getSettings()
    {
        return $this->settings ?? Settings::instance();
    }

    public function onRun()
    {
        if (isset($this->page->apiBag['staticPage'])) {
            $this->seoAttributes = $this->page['viewBag'] = array_merge(
                $this->page->apiBag['staticPage']->viewBag,
                $this->page->attributes
            );
        } else {
            $this->seoAttributes = $this->page->settings;
        }
    }

    public function getSeoAttribute($seoAttribute)
    {
        return $this->seoAttributes['seo_options_' . $seoAttribute] ?? $this->seoAttributes[$seoAttribute] ?? null;
    }

    public function getTitleRaw()
    {
        return $this->getPropertyTranslated('meta_title') ?: $this->getSeoAttribute('title') ?: null;
    }

    /**
     * Returns the title of the page taking position from settings into consideration
     *
     * @return string title of the page;
     */
    public function getTitle()
    {
        $title = $this->getTitleRaw();

        $settings = $this->getSettings();

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
            $description = $this->getSeoAttribute('description') ?? null;
        }

        if (!$description) {
            $settings = $this->getSettings();
            $description = $settings->site_description;
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
     * Returns og_ref_image if set
     * else og_image if set
     * else fallback to getSiteImageFromSettings()
     *
     * @return string
     */
    public function getOgImage()
    {
        if ($ogImage = $this->getPropertyTranslated('og_ref_image')) {
            return $ogImage;
        }

        if ($ogImage = $this->getPropertyTranslated('og_image')) {
            if (class_exists(MediaLibrary::class)) {
                return $ogImage;
            }

            return OldMediaLibrary::instance()->getPathUrl($ogImage);
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
        return $this->getSeoAttribute('og_video') ?? null;
    }

    /**
     * Returns og_type if set in the viewBag
     * otherwise returns string 'website'
     *
     * @return string default 'website'
     */
    public function getOgType()
    {
        return $this->getSeoAttribute('og_type') ?? 'website';
    }

    /**
     * Returns og_card if set in the viewBag
     * otherwise returns string 'summary_large_image'
     *
     * @return string default 'summary_large_image'
     */
    public function getOgCard()
    {
        return $this->getSeoAttribute('og_card') ?? 'summary_large_image';
    }

    /**
     * Returns the URL of the site image
     *
     * @return string site image url
     */
    public function getSiteImageFromSettings()
    {
        $settings = $this->getSettings();
        $siteImageFrom = $settings->site_image_from;

        if ($siteImageFrom === 'media' && $settings->site_image) {
            return MediaLibrary::instance()->getPathUrl($settings->site_image);
        } elseif ($siteImageFrom === "fileupload") {
            return $settings->site_image_fileupload()->getSimpleValue();
        } elseif ($siteImageFrom === "url") {
            return $settings->site_image_url;
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
        $localizedKey = 'locale' . strtolower($viewBagProperty);
        return $this->getSeoAttribute($localizedKey)[$locale] ?? $this->getSeoAttribute($viewBagProperty) ?? null;
    }
}
