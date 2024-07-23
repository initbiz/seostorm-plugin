<?php

namespace Initbiz\Seostorm\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Backend\Classes\SettingsController;

/**
 * Sitemap Media Backend Controller
 *
 * @link https://docs.octobercms.com/3.x/extend/system/controllers.html
 */
class SitemapMedia extends SettingsController
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    public $settingsItemCode = 'sitemap_item';

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['initbiz.seostorm.sitemapmedia'];

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();
    }
}
