<?php

namespace Initbiz\SeoStorm\Models;

use Site;
use Cache;
use System\Models\File;
use Media\Classes\MediaLibrary;
use System\Models\SettingModel;
use System\Classes\SiteCollection;

class Settings extends SettingModel
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Multisite;


    /**
     * Cache key to store current favicon hash (to check if it was updated)
     */
    public const FAVICON_CACHE_KEY = 'initbiz-seostorm-favicon-cache-key';

    public $implement = [
        '@' . \RainLab\Translate\Behaviors\TranslatableModel::class,
    ];

    public $translatable = [
        'site_name',
        'site_description',
        'extra_meta',
        'site_image',
        'og_locale',
        'robots_txt',
    ];

    public $propagatable = [
        'enable_sitemap',
        'enable_index_sitemap',
        'enable_images_sitemap',
        'enable_videos_sitemap',
        'sitemap_enabled_for_sites',
        'enable_robots_txt',
        'enable_robots_meta',
        'favicon_enabled',
        'favicon_from',
        'favicon',
        'favicon_fileupload',
        'favicon_url',
        'favicon_sizes',
        'webmanifest_enabled',
        'webmanifest_name',
        'webmanifest_short_name',
        'webmanifest_background_color',
        'webmanifest_theme_color',
        'webmanifest_display',
        'webmanifest_custom_attributes',
        'enable_og',
        'enable_og',
        'site_image_from',
        'site_image_fileupload',
        'site_image_url',
        'og_site_name',
        'fb_app_id',
        'twitter_site',
        'twitter_creator',
        'og_locale_alternate',
        'schema_image_from',
        'schema_image',
        'schema_image_fileupload',
        'schema_image_url',
        'publisher_type',
        'publisher_same_as',
        'publisher_name',
        'publisher_logo_url',
        'publisher_url',
    ];

    protected $propagatableSync = true;

    public $attachOne = [
        'site_image_fileupload' => [
            \System\Models\File::class,
        ],
        'schema_image_fileupload' => [
            \System\Models\File::class,
        ],
        'favicon_fileupload' => [
            \System\Models\File::class,
        ],
    ];

    // A unique code
    public $settingsCode = 'initbiz_seostorm_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public $rules = [
        'favicon_url' => 'required_if:favicon_from,url',
    ];

    public function initSettingsData()
    {
        $this->enable_site_meta = true;
        $this->site_name_position = 'nowhere';

        $this->enable_sitemap = true;
        $this->enable_images_sitemap = false;
        $this->enable_videos_sitemap = false;

        $this->enable_robots_txt = true;
        $this->enable_robots_meta = true;
        $this->robots_txt = 'User-agent: *\r\nAllow: /';

        $this->favicon_enabled = false;
        $this->favicon_from = 'fileupload';
        $this->webmanifest_enabled = false;

        $this->enable_og = true;
        $this->publisher_type = 'Organization';
    }

    public function beforeSave()
    {
        if (empty($this->favicon_from)) {
            $this->favicon_from = 'fileupload';
        }

        // Cleanup
        if ($this->favicon_from === 'media') {
            $this->favicon_url = null;
        } elseif ($this->favicon_from === 'url') {
            $this->favicon = null;
        } elseif ($this->favicon_from === 'fileupload') {
            $this->favicon_url = null;
            $this->favicon = null;
        }

        $favicon = $this->favicon_fileupload;

        $contents = '';
        if ($favicon instanceof File) {
            $contents = $favicon->getContents();
        }

        $hash = sha1($this->favicon_from . $contents);
        $cachedHash = Cache::get(self::FAVICON_CACHE_KEY);

        // Favicon hasn't been changed since last saving
        if ($hash === $cachedHash) {
            return;
        }

        if ($this->favicon_from === 'media' || $this->favicon_from === 'url') {
            $url = $this->favicon_url;
            if ($this->favicon_from === 'media' && !empty($this->favicon)) {
                $url = MediaLibrary::url($this->favicon);
            }

            if (empty($url)) {
                return;
            }

            $faviconInstance = (new File())->fromUrl($url);
            $faviconInstance->save();
            $this->favicon_fileupload()->add($faviconInstance);
        }

        Cache::put(self::FAVICON_CACHE_KEY, $hash);
    }

    public function getSitemapEnabledForSitesOptions()
    {
        $options = [];

        foreach (Site::listSites() as $siteDefinition) {
            if ($siteDefinition->is_primary) {
                continue;
            }

            $prefix = empty($siteDefinition->route_prefix) ? '/' : $siteDefinition->route_prefix;
            $options[$siteDefinition->code] = $siteDefinition->name . ' (' . $prefix . ')';
        }

        return $options;
    }

    /**
     * Return all enabled sites in the sitemap + the primary one
     *
     * @return SiteCollection
     */
    public function getSitesEnabledInSitemap(): SiteCollection
    {
        $codes = $this->get('sitemap_enabled_for_sites');
        if (empty($codes)) {
            $codes = [];
        }

        // Always include primary site
        $primarySiteCode = Site::getPrimarySite()->code;
        if (!in_array($primarySiteCode, $codes)) {
            $codes[] = $primarySiteCode;
        }

        return Site::listSites()->whereIn('code', $codes);
    }

    public function filterFields($fields): void
    {
        // Display sitemap_enabled_for_sites only if there are more than one site
        if (!isset($fields->sitemap_enabled_for_sites)) {
            return;
        }

        if (Site::listSites()->count() > 1) {
            $fields->sitemap_enabled_for_sites->hidden = false;
        }
    }

    /**
     * Get Favicon object depending on type selected - for media and URL we'll generate it
     *
     * @return File|null
     */
    public function getFaviconObject(): ?File
    {
        // Backward compatibility
        if (!empty($this->favicon) && empty($this->favicon_from)) {
            $this->favicon_from = 'media';
            $this->save();
        }

        // in beforeSave event, we're setting favicon_fileupload for every type: media, and url, too
        return $this->favicon_fileupload;
    }
}
