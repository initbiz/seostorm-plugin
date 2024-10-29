<?php

namespace Initbiz\SeoStorm;

use Event;
use Cms\Twig\Extension;
use Cms\Classes\Controller;
use System\Classes\PluginBase;
use Initbiz\SeoStorm\Classes\Router;
use Initbiz\SeoStorm\Models\Htaccess;
use Initbiz\SeoStorm\Models\Settings;
use Twig\Extension\StringLoaderExtension;

/**
 * Initbiz Plugin Information File
 */
class Plugin extends PluginBase
{
    public function __construct($app)
    {
        $parent = parent::__construct($app);

        if (app()->runningUnitTests()) {
            $this->require = array_merge($this->require, ['RainLab.Translate']);
        }

        return $parent;
    }

    public function registerComponents()
    {
        return [
            'Initbiz\SeoStorm\Components\Seo' => 'seo',
            'Initbiz\SeoStorm\Components\Schema' => 'schema',
        ];
    }

    public function register()
    {
        $this->registerConsoleCommand('migrate:arcane', \Initbiz\SeoStorm\Console\MigrateArcane::class);
        $this->registerConsoleCommand('sitemap:refresh', \Initbiz\SeoStorm\Console\SitemapRefresh::class);
    }

    public function boot()
    {
        (new Router())->register();

        Event::subscribe(\Initbiz\SeoStorm\EventHandlers\BackendHandler::class);
        Event::subscribe(\Initbiz\SeoStorm\EventHandlers\StormedHandler::class);
        Event::subscribe(\Initbiz\SeoStorm\EventHandlers\RainlabPagesHandler::class);
        Event::subscribe(\Initbiz\SeoStorm\EventHandlers\RainlabTranslateHandler::class);
        Event::subscribe(\Initbiz\SeoStorm\EventHandlers\SitemapHandler::class);

        // Load Twig extensions

        /**
         * @see \Modules\System\ServiceProvider to method registerTwigParser for more info
         */
        $twig = app()->get('twig.environment');

        if (!$twig->hasExtension(StringLoaderExtension::class)) {
            $stringLoader = new StringLoaderExtension();
            $twig->addExtension($stringLoader);
        }

        if (!$twig->hasExtension(Extension::class)) {
            $controller = Controller::getController() ?? new Controller();
            $octoberExtensions = new Extension($controller);
            $twig->addExtension($octoberExtensions);
        }
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'initbiz.seostorm::lang.form.settings.label',
                'description' => 'initbiz.seostorm::lang.form.settings.description',
                'icon'        => 'icon-search',
                'category'    => 'initbiz.seostorm::lang.form.settings.category_label',
                'class'       => Settings::class,
                'order'       => 100,
                'permissions' => ['initbiz.manage_seo'],
            ],
            'htaccess' => [
                'label'       => 'initbiz.seostorm::lang.form.htaccess.label',
                'description' => 'initbiz.seostorm::lang.form.htaccess.description',
                'icon'        => 'icon-file-text-o',
                'category'    => 'initbiz.seostorm::lang.form.settings.category_label',
                'class'       => Htaccess::class,
                'order'       => 200,
                'permissions' => ['initbiz.manage_seo'],
            ],
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                // See https://github.com/initbiz/seostorm-plugin/issues/82 for explanation
                'template_from_string' => [$this, 'templateFromString'],
                'templateFromString' => [$this, 'templateFromString'],
            ]
        ];
    }

    /**
     * Extend twig to parse twig from twig with StringLoaderExtension
     *
     * @param string $template
     * @return string
     */
    public function templateFromString($template)
    {
        if (is_null($template)) {
            $template = '';
        }

        $twig = app()->get('twig.environment');
        return StringLoaderExtension::templateFromString($twig, $template);
    }

    /**
     * Register models that are stormed by default
     *
     * @return array
     */
    public function registerStormedModels()
    {
        $modelDefs = [
            \Rainlab\Blog\Models\Post::class => [
                'placement' => 'secondaryTabs',
                'excludeFields' => [
                    'model_class',
                    'model_scope',
                    'model_params',
                    'lastmod',
                    'use_updated_at',
                    'changefreq',
                    'priority',
                    'enabled_in_sitemap',
                ],
            ],

            \Rainlab\Blog\Models\Category::class => [
                'placement' => 'tabs',
                'excludeFields' => [
                    'model_class',
                    'model_scope',
                    'model_params',
                    'lastmod',
                    'use_updated_at',
                    'changefreq',
                    'priority',
                    'enabled_in_sitemap',
                ],
            ],
        ];

        if (\App::runningUnitTests()) {
            $modelDefs['\Initbiz\SeoStorm\Tests\Classes\FakeStormedModel'] = [
                'placement' => 'tabs',
                'excludeFields' => [
                    'changefreq',
                ],
            ];
        }

        return $modelDefs;
    }

    public function registerFormWidgets()
    {
        return [
            \Initbiz\SeoStorm\FormWidgets\Migrate::class => 'seo_migrate'
        ];
    }
}
