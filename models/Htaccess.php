<?php

namespace Initbiz\SeoStorm\Models;

use File;
use Model;

class Htaccess extends Model
{
    public $implement = [
        'System.Behaviors.SettingsModel',
    ];

    // A unique code
    public $settingsCode = 'initbiz_seostorm_htaccess';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->htaccess = File::get(base_path(".htaccess"));
    }

    public function afterSave()
    {
        $htaccess = $this->value["htaccess"];
        File::put(base_path(".htaccess"), $htaccess);
    }
}
