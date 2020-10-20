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

    protected $cache = [];

    public function getPageOptions()
    {
        return \Cms\Classes\Page::getNameList();
    }

    public function initSettingsData()
    {
        $this->htaccess = \File::get(base_path(".htaccess"));
    }
}
