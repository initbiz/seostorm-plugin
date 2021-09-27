<?php

namespace Initbiz\SeoStorm;

use Event;
use System\Classes\PluginBase;
use Initbiz\SeoStorm\Models\Htaccess;
use Initbiz\SeoStorm\Models\Settings;
use Twig\Extension\StringLoaderExtension;

/**
 * Initbiz Plugin Information File
 */
class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Initbiz\SeoStorm\Components\Seo' => 'seo',
        ];
    }

    public function boot()
    {
        Event::subscribe(\Initbiz\SeoStorm\EventHandlers\BackendHandler::class);
        Event::subscribe(\Initbiz\SeoStorm\EventHandlers\StormedHandler::class);
        Event::subscribe(\Initbiz\SeoStorm\EventHandlers\RainlabPagesHandler::class);
        Event::subscribe(\Initbiz\SeoStorm\EventHandlers\RainlabTranslateHandler::class);
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
            ]
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'template_from_string' => [$this, 'templateFromString'],
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
        $twig = app()->get('twig.environment');

        if (!$twig->hasExtension(StringLoaderExtension::class)) {
            $stringLoader = new StringLoaderExtension();
            $twig->addExtension($stringLoader);
        }

        return twig_template_from_string($twig, $template);
    }

    /**
     * Register models that are stormed by default
     *
     * @return array
     */
    public function registerStormedModels()
    {
        $modelDefs = [
            'Rainlab\Blog\Models\Post' => [
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
        ];

        if (env('APP_ENV') === 'testing') {
            $modelDefs['\Initbiz\SeoStorm\Tests\Classes\FakeStormedModel'] = [
                'placement' => 'tabs',
            ];
        }

        return $modelDefs;
    }
}
