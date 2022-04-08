<?php

namespace Initbiz\SeoStorm\Components;

use App;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Media\Classes\MediaLibrary;
use Initbiz\SeoStorm\Models\Settings;

class Seo extends ComponentBase
{
    /**
     * Current attributes parsed from settings or viewBag (in static pages)
     *
     * @var Array
     */
    public array $seoAttributes;

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
        if (isset($this->settings)) {
            return $this->settings;
        }

        $this->settings = Settings::instance();

        return $this->settings;
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

    // Site meta getters

    public function getRobots($advancedRobots = '')
    {
        $robots = [];
        if (!empty($index = $this->getSeoAttribute('robotIndex'))) {
            $robots[] = $index;
        }

        if (!empty($follow = $this->getSeoAttribute('robotFollow'))) {
            $robots[] = $follow;
        }

        if (!empty($advancedRobots)) {
            $robots[] = $advancedRobots;
        }

        return implode(',', $robots);
    }

    public function getCanonicalUrl($parsedTwig = '')
    {
        $url = Page::url($this->page->id);

        if (isset($this->page->apiBag['staticPage'])) {
            $url = url($this->seoAttributes['url']);
        }

        if (!empty($parsedTwig)) {
            $url = url($parsedTwig);
        }

        return $url;
    }

    /**
     * Get the title of the page without suffix/prefix from the settings
     *
     * @return string
     */
    public function getTitleRaw()
    {
        return $this->getPropertyTranslated('metaTitle') ?: $this->getSeoAttribute('title') ?: null;
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
        $description = $this->getPropertyTranslated('metaDescription');

        if (!$description) {
            $description = $this->getSeoAttribute('description') ?? null;
        }

        if (!$description) {
            $settings = $this->getSettings();
            $description = $settings->site_description;
        }

        return $description;
    }

    // Open Graph parameter getters

    /**
     * Returns og_title if set in the viewBag
     * otherwise fallback to getTitle
     *
     * @return string social media title
     */
    public function getOgTitle()
    {
        return $this->getPropertyTranslated('ogTitle') ?? $this->getTitle();
    }

    /**
     * Returns ogDescription if is set in the ViewBag
     * otherwise fallback to getDescription
     *
     * @return string social media description
     */
    public function getOgDescription()
    {
        return $this->getPropertyTranslated('ogDescription') ?? $this->getDescription();
    }

    /**
     * Returns ogRefImage if set
     * else ogImage if set
     * else fallback to getSiteImageFromSettings()
     *
     * @return string
     */
    public function getOgImage()
    {
        if ($ogImage = $this->getPropertyTranslated('ogRefImage')) {
            return $ogImage;
        }

        if ($ogImage = $this->getPropertyTranslated('ogImage')) {
            return $ogImage;
        }

        return $this->getSiteImageFromSettings();
    }

    /**
     * Returns ogVideo if set in the viewBag
     *
     * @return string
     */
    public function getOgVideo()
    {
        return $this->getSeoAttribute('ogVideo') ?? null;
    }

    /**
     * Returns ogType if set in the viewBag
     * otherwise returns string 'website'
     *
     * @return string default 'website'
     */
    public function getOgType()
    {
        return $this->getSeoAttribute('ogType') ?? 'website';
    }

    /**
     * Returns ogCard if set in the viewBag
     * otherwise returns string 'summary_large_image'
     *
     * @return string default 'summary_large_image'
     */
    public function getOgCard()
    {
        return snake_case($this->getSeoAttribute('ogCard') ?? 'summary_large_image');
    }

    /**
     * Returns twitter_site if set in the viewBag or settings
     *
     * @return ?string
     */
    public function getTwitterSite(): ?string
    {
        $twitterSite = $this->getSeoAttribute('twitter_site') ?? null;

        if (!$twitterSite) {
            $twitterSite = $this->getSettings()->twitter_site;
        }

        return $twitterSite;
    }

    /**
     * Returns twitter_creator if set in the viewBag or settings
     *
     * @return ?string
     */
    public function getTwitterCreator(): ?string
    {
        $twitterCreator = $this->getSeoAttribute('twitter_creator') ?? null;

        if (!$twitterCreator) {
            $twitterCreator = $this->getSettings()->twitter_creator;
        }

        return $twitterCreator;
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

    // Global helpers

    /**
     * Getter for attributes set in the page's settings
     *
     * @param string $seoAttribute name of the seo attribute e.g. canonicalUrl
     * @return null|string
     */
    public function getSeoAttribute(string $seoAttribute): ?string
    {
        return $this->seoAttributes['seoOptions' . studly_case($seoAttribute)]
            ?? $this->seoAttributes[snake_case($seoAttribute)]
            ?? null;
    }

    /**
     * Setter for seoAttributes. If you need to change attributes,
     * you can do it use this method.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setSeoAttribute(string $key, string $value): void
    {
        if (!is_array($this->seoAttributes)) {
            $this->seoAttributes = [];
        }
        $this->seoAttributes[$key] = $value;
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
