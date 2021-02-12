<?php

namespace Initbiz\SeoStorm\Models;

use Model;

class Htaccess extends Model
{
    public $implement = [
        'System.Behaviors.SettingsModel',
        '@RainLab.Translate.Behaviors.TranslatableModel',
    ];
    
    // A unique code
    public $settingsCode = 'initbiz_seostorm_htaccess';

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
