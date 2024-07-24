<?php

namespace Initbiz\SeoStorm\Models;

use Site;
use Model;
use RainLab\Translate\Classes\Locale;

class Settings extends Model
{
    public $implement = [
        'System.Behaviors.SettingsModel',
        '@RainLab.Translate.Behaviors.TranslatableModel',
    ];

    public $translatable = [
        'site_name',
        'site_description',
        'extra_meta',
        'site_image',
        'og_locale',
    ];

    public $attachOne = [
        'site_image_fileupload' => [
            '\System\Models\File',
        ],

        'schema_image_fileupload' => [
            '\System\Models\File',
        ],
    ];

    // A unique code
    public $settingsCode = 'initbiz_seostorm_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->enable_site_meta = true;
        $this->site_name_position = 'nowhere';
        $this->enable_sitemap = true;
        $this->enable_robots_txt = true;
        $this->enable_robots_meta = true;
        $this->enable_robots_txt = 'User-agent: *\r\nAllow: /';
        $this->favicon_enabled = false;
        $this->favicon_16 = false;
        $this->enable_og = true;
        $this->publisher_type = 'Organization';
        $this->enable_image_in_sitemap = false;
        $this->enable_video_in_sitemap = false;
    }

    public function getSitemapLocalesOptions()
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

    public function getSitesEnabledInSitemap()
    {
        return Site::listSites()->whereIn('code', $this->sitemap_locales);
    }

    public function filterFields($fields): void
    {
        if (!isset($fields->sitemap_locales)) {
            return;
        }

        if (Site::listSites()->count() > 1) {
            $fields->sitemap_locales->hidden = false;
        }
    }
}
