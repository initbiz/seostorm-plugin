<?php

namespace Initbiz\SeoStorm\Models;

use Model;

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
    }
}
