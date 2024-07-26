<?php

namespace Initbiz\Seostorm\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Illuminate\Support\Facades\Artisan;

/**
 * SitemapItem Backend Controller
 *
 * @link https://docs.octobercms.com/3.x/extend/system/controllers.html
 */
class SitemapItems extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    /**
     * @var string settingsItemCode determines the settings code
     */
    public $settingsItemCode = 'sitemap_item';
    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';

    public $relationConfig = 'config_relation.yaml';

    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['initbiz.seostorm.sitemapitem'];

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Initbiz.Seostorm', 'settings');
    }

    public function onRefresh()
    {
        Artisan::call('sitemap:refresh');
    }
}
