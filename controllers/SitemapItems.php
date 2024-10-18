<?php

namespace Initbiz\Seostorm\Controllers;

use Request;
use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request as HttpRequest;

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
    public $requiredPermissions = ['initbiz.seostorm.sitemapitems'];

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
        // We need to temporarily replace request with faked one to get valid URLs
        $originalRequest = Request::getFacadeRoot();
        $originalHost = parse_url($originalRequest->url())['host'];

        $request = new HttpRequest();
        $request->headers->set('host', $originalHost);

        Request::swap($request);

        try {
            Artisan::call('sitemap:refresh');
        } catch (\Throwable $th) {
            Request::swap($originalRequest);
            throw $th;
        }

        Request::swap($originalRequest);
    }
}
