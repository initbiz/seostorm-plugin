<?php

namespace Arcane\Seo;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Arcane\Seo\Classes\Helper;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use System\Classes\SettingsManager;

/**
 * Arcane Plugin Information File
 */
class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Arcane\Seo\Components\Seo' => 'seo',
            'Arcane\Seo\Components\SchemaVideo' => 'schemaVideo',
            'Arcane\Seo\Components\SchemaArticle' => 'schemaArticle',
            'Arcane\Seo\Components\SchemaProduct' => 'schemaProduct',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Arcane SEO settings',
                'description' => 'Configure Arcane SEO',
                'icon'        => 'icon-search',
                'category'    =>  SettingsManager::CATEGORY_CMS,
                'class'       => 'Arcane\Seo\Models\Settings',
                'order'       => 100,
                'permissions' => ['arcane.manage_seo'],
            ]
        ];
    }

    public function registerMarkupTags()
    {
        $helper = new Helper();
        $minifier = \Arcane\Seo\Classes\Minifier::class;
        $schema = \Arcane\Seo\Classes\Schema::class;
        return [
            'filters' => [
                'minifyjs' => [$minifier, 'minifyJs'],
                'minifycss' => [$minifier, 'minifyCss'],
                'arcane_seo_schema' => [$schema, 'toScript'],
                'seotitle'    => [$helper, 'generateTitle'],
                'removenulls' => [$helper, 'removeNullsFromArray'],
                'fillparams'  => ['Arcane\Seo\Classes\Helper', 'replaceUrlPlaceholders'],
                'url' => [$helper, 'url'],
            ]
        ];
    }

    public function registerPageSnippets()
    {
        return [
            '\Arcane\Seo\Components\SchemaVideo' => 'schemaVideo',
            '\Arcane\Seo\Components\SchemaArticle' => 'schemaArticle',
            '\Arcane\Seo\Components\SchemaProduct' => 'schemaProduct',
        ];
    }

    public function register()
    {
        \Event::listen('backend.form.extendFieldsBefore', function ($widget) {
            if ($widget->isNested === false) {

                if (!Theme::getEditTheme())
                    throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));

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
                            'arcane_seo_options[model_class]',
                            'arcane_seo_options[lastmod]',
                            'arcane_seo_options[use_updated_at]',
                            'arcane_seo_options[changefreq]',
                            'arcane_seo_options[priority]'
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
            return ["arcane_seo_options[$key]" => $item];
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
        $fields = \Yaml::parseFile(plugins_path('arcane/seo/config/seofields.yaml'));

        $user = \BackendAuth::getUser();

        if ($user) {
            $fields = array_except(
                $fields,
                array_merge(
                    [],
                    !$user->hasPermission(["arcane.seo.og"]) ? [
                        "og_title",
                        "og_description",
                        "og_image",
                        "og_type",
                        "og_ref_image"
                    ] : [],
                    !$user->hasPermission(["arcane.seo.sitemap"]) ? [
                        "enabled_in_sitemap",
                        "model_class",
                        "use_updated_at",
                        "lastmod",
                        "changefreq",
                        "priority",
                    ] : [],
                    !$user->hasPermission(["arcane.seo.meta"]) ? [
                        "meta_title",
                        "meta_description",
                        "canonical_url",
                        "robot_index",
                        "robot_follow",
                        "robot_advanced",
                    ] : [],
                    !$user->hasPermission(["arcane.seo.schema"]) ? [
                        "schemas"
                    ] : []
                )
            );
        }

        return $fields;
    }
}
