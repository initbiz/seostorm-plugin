<?php

namespace Initbiz\SeoStorm;

use Lang;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use System\Classes\SettingsManager;
use Initbiz\SeoStorm\Classes\Helper;
use Twig\Extension\StringLoaderExtension;
use October\Rain\Exception\ApplicationException;

/**
 * Initbiz Plugin Information File
 */
class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Initbiz\SeoStorm\Components\Seo' => 'seo',
            'Initbiz\SeoStorm\Components\SchemaVideo' => 'schemaVideo',
            'Initbiz\SeoStorm\Components\SchemaArticle' => 'schemaArticle',
            'Initbiz\SeoStorm\Components\SchemaProduct' => 'schemaProduct',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'initbiz.seostorm::lang.form.settings.label',
                'description' => 'initbiz.seostorm::lang.form.settings.description',
                'icon'        => 'icon-search',
                'category'    =>  SettingsManager::CATEGORY_CMS,
                'class'       => 'Initbiz\SeoStorm\Models\Settings',
                'order'       => 100,
                'permissions' => ['initbiz.manage_seo'],
            ]
        ];
    }

    public function registerMarkupTags()
    {
        $helper = new Helper();
        $minifier = \Initbiz\SeoStorm\Classes\Minifier::class;
        $schema = \Initbiz\SeoStorm\Classes\Schema::class;
        return [
            'filters' => [
                'minifyjs' => [$minifier, 'minifyJs'],
                'minifycss' => [$minifier, 'minifyCss'],
                'initbiz_seostorm_schema' => [$schema, 'toScript'],
                'removenulls' => [$helper, 'removeNullsFromArray'],
                'fillparams'  => ['Initbiz\SeoStorm\Classes\Helper', 'replaceUrlPlaceholders'],
                'url' => [$helper, 'url'],
            ],
            'functions' => [
                'template_from_string' => [$this, 'templateFromString'],
            ]
        ];
    }

    public function templateFromString($template)
    {
        $twig = $this->app->make('twig.environment');

        if (!$twig->hasExtension(StringLoaderExtension::class)) {
            $stringLoader = new StringLoaderExtension();
            $twig->addExtension($stringLoader);
        }

        return twig_template_from_string($twig, $template);
    }

    public function registerPageSnippets()
    {
        return [
            '\Initbiz\SeoStorm\Components\SchemaVideo' => 'schemaVideo',
            '\Initbiz\SeoStorm\Components\SchemaArticle' => 'schemaArticle',
            '\Initbiz\SeoStorm\Components\SchemaProduct' => 'schemaProduct',
        ];
    }

    public function register()
    {
        $this->registerConsoleCommand('migrate:arcane', 'Initbiz\SeoStorm\Console\MigrateArcane');

        \Event::listen('backend.form.extendFieldsBefore', function ($widget) {
            if ($widget->isNested === false) {

                if (!Theme::getEditTheme()) {
                    throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
                }

                if (
                    PluginManager::instance()->hasPlugin('RainLab.Pages')
                    && $widget->model instanceof \RainLab\Pages\Classes\Page
                ) {
                    $widget->tabs['fields'] = array_replace(
                        $widget->tabs['fields'],
                        array_except($this->staticSeoFields(), [
                            'viewBag[model_class]',
                        ])
                    );
                }

                if (
                    PluginManager::instance()->hasPlugin('RainLab.Blog')
                    && $widget->model instanceof \RainLab\Blog\Models\Post
                ) {

                    $widget->secondaryTabs['fields'] = array_replace(
                        $widget->secondaryTabs['fields'],
                        array_except($this->blogSeoFields(), [
                            'initbiz_seostorm_options[model_class]',
                            'initbiz_seostorm_options[lastmod]',
                            'initbiz_seostorm_options[use_updated_at]',
                            'initbiz_seostorm_options[changefreq]',
                            'initbiz_seostorm_options[priority]'
                        ])
                    );
                }

                if (!$widget->model instanceof Page) return;

                $widget->tabs['fields'] = array_replace($widget->tabs['fields'], $this->cmsSeoFields());
            }
        });

        if (PluginManager::instance()->hasPlugin('RainLab.Translate')) {
            Page::extend(function ($model) {
                if (!$model->propertyExists('translatable')) {
                    $model->addDynamicProperty('translatable', []);
                }
                $model->translatable = array_merge($model->translatable, $this->seoFieldsToTranslate());
            });
        }
    }

    protected function seoFieldsToTranslate()
    {
        $toTrans = [];
        foreach ($this->seoFields() as $fieldKey => $fieldValue) {
            if (isset($fieldValue['trans']) && $fieldValue['trans'] == true) {
                $toTrans[] = $fieldKey;
            }
        }
        return $toTrans;
    }

    protected function blogSeoFields()
    {
        return collect($this->seoFields())->mapWithKeys(function ($item, $key) {
            return ["initbiz_seostorm_options[$key]" => $item];
        })->toArray();
    }

    protected function staticSeoFields()
    {
        return collect($this->seoFields())->mapWithKeys(function ($item, $key) {
            return ["viewBag[$key]" => $item];
        })->toArray();
    }

    protected function cmsSeoFields()
    {
        return collect($this->seofields())->mapWithKeys(function ($item, $key) {
            return ["settings[$key]" => $item];
        })->toArray();
    }

    protected function seoFields()
    {
        $fields = \Yaml::parseFile(plugins_path('initbiz/seostorm/config/seofields.yaml'));

        $user = \BackendAuth::getUser();

        if ($user) {
            $fields = array_except(
                $fields,
                array_merge(
                    [],
                    !$user->hasPermission(["initbiz.seostorm.og"]) ? [
                        "og_title",
                        "og_description",
                        "og_image",
                        "og_type",
                        "og_ref_image"
                    ] : [],
                    !$user->hasPermission(["initbiz.seostorm.sitemap"]) ? [
                        "enabled_in_sitemap",
                        "model_class",
                        "use_updated_at",
                        "lastmod",
                        "changefreq",
                        "priority",
                    ] : [],
                    !$user->hasPermission(["initbiz.seostorm.meta"]) ? [
                        "meta_title",
                        "meta_description",
                        "canonical_url",
                        "robot_index",
                        "robot_follow",
                        "robot_advanced",
                    ] : [],
                    !$user->hasPermission(["initbiz.seostorm.schema"]) ? [
                        "schemas"
                    ] : []
                )
            );
        }

        return $fields;
    }
}
