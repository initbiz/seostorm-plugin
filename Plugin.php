<?php namespace Arcane\Seo;

use System\Classes\PluginBase;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use System\Classes\SettingsManager;
use Arcane\Seo\Classes\Helper;
use Cms\Classes\Controller;

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
                'label'       => 'SEO',
                'description' => 'Configure the SEO of your site',
                'icon'        => 'icon-search',
                'category'    =>  SettingsManager::CATEGORY_CMS,
                'class'       => 'Arcane\Seo\Models\Settings',
                'order'       => 100,
                'permissions' => [ 'arcainz.arcane.manage_seo' ],
            ]
        ];

    }

    public function registerMarkupTags()
    {
        $helper = new Helper();
        return [
            'filters' => [
                'seotitle'             => [$helper, 'generateTitle'],
                'removenulls'           => [$helper, 'removeNullsFromArray'],
                'fillparams'            => ['Arcane\Seo\Classes\Helper', 'replaceUrlPlaceholders'],
                't'            => ['Arcane\Seo\Classes\Helper', 'parseIfTwigSyntax'],
                'url'            => [$helper, 'url'],
                'duration'            => [$helper, 'duration'],
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

    public function boot() {
        if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
        {
            \RainLab\Blog\Models\Post::extend(function($model) {
                $model->addJsonable('seo_options');
            });
        }

    }
    

    public function register()
    {
        \Event::listen('backend.form.extendFields', function($widget)
        {
            if (!($theme = Theme::getEditTheme())) throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
            if(PluginManager::instance()->hasPlugin('RainLab.Pages') && $widget->model instanceof \RainLab\Pages\Classes\Page)
           {  
                $widget->removeField('viewBag[meta_title]' );
                $widget->removeField('viewBag[meta_description]');
                $widget->addFields( array_except($this->staticSeoFields(), [
                    'viewBag[model_class]', 'viewBag[use_updated_at]'
                ]), 'primary'); 
            }
            
            if(PluginManager::instance()->hasPlugin('RainLab.Blog') && $widget->model instanceof \RainLab\Blog\Models\Post)
                $widget->addFields( array_except($this->blogSeoFields(), [
                    'seo_options[model_class]', 'seo_options[lastmod]', 'seo_options[use_updated_at]', 'seo_options[sitemap_section]', 'seo_options[changefreq]', 'seo_options[priority]'
                ]), 'secondary');
            
            if (!$widget->model instanceof \Cms\Classes\Page) return;

            $widget->removeField('settings[meta_title]');
            $widget->removeField('settings[meta_description]');
            $widget->addFields( $this->cmsSeoFields(), 'primary');

        });
    }

    
    private function blogSeoFields() {
        return collect($this->seoFields())->mapWithKeys(function($item, $key) {
            return ["seo_options[$key]" => $item];
        })->toArray();
    }
    private function staticSeoFields() {
        return collect($this->seoFields())->mapWithKeys(function($item, $key) {
            return ["viewBag[$key]" => $item];
        })->toArray();
    }
    private function cmsSeoFields() {
        return collect($this->seofields())->mapWithKeys(function($item, $key) {
            return ["settings[$key]" => $item];
        })->toArray();
    }
    private function seoFields() {
        return [
            'model_class' => [
                'label'   => 'Model class',
                'type'    => 'text',
                'span'    => 'full',
                'comment' => 'Associate this page with a model, it can be used to generate the sitemap.xml links, or to use it\'s field values for SEO settings. ',
                'placeholder' => 'Example: RainLab\Blog\Models\Post',
                'tab'     => 'cms::lang.editor.settings',
            ],
            'meta_title' => [
                'label'   => 'SEO Title',
                'type'    => 'text',
                'span' => 'auto',
                'tab'     => 'SEO',
                'comment' => 'Should be no more than 60 characters',
                'attributes' => [ 'data-seo' => 'title']
            ],
            'meta_description' => [
                'label' => 'SEO Description',
                'type' => 'textarea',
                'size' => 'large',
                'span' => 'right',
                'tab' => 'SEO',
                'comment' => 'Recommended characters: <= 160',
                'attributes' => [ 'data-seo' => 'description']
            ],
            'meta_keywords' => [
                'label'   => 'SEO Keywords',
                'type'    => 'text',
                'span' => 'left',
                'tab'     => 'SEO',
            ],
            'canonical_url' => [
                'label'   => 'Canonical URL',
                'type'    => 'text',
                'tab'     => 'SEO',
                'span'    => 'left',
                'comment' => 'The canonical URL this page should point to, defaults to current url'
            ],
            'robot_index' => [
                'label'   => 'Meta robots index',
                'type'    => 'balloon-selector',
                'tab'     => 'SEO',
                'options' => [
                    'index' => 'index',
                    'noindex' => 'noindex'
                ],
                'comment' => 'Specify if search engines should index this page',
                'span'    => 'left'
            ],
            'robot_follow' => [
                'label'   => 'Meta robots follow',
                'type'    => 'balloon-selector',
                'tab'     => 'SEO',
                'options' => [
                    'follow' => 'follow',
                    'nofollow' => 'nofollow',
                ],
                'comment' => 'Specify if search engines should follow the links on this page',
                'span'    => 'right'
            ],
            'robot_advanced' => [
                'label'   => 'Advanced robots',
                'tab'     => 'SEO',
                'comment' => 'Add aditional directives to the robots meta tag, separated by commas',
                'span' => 'left',
            ],
            'sitemap_section' => [
                'label'   => 'Sitemap',
                'type'    => 'section',
                'tab'     => 'SEO',
                'span'    => 'full'
            ],
            'enabled_in_sitemap' => [
                'label'   => 'Enable in the sitemap.xml',
                'comment' => 'Page will appear in the sitemap.xml',
                'type'    => 'checkbox',
                'default' => true,
                'tab'     => 'SEO',
                'span' => 'left'
            ],
            'use_updated_at' => [
                'label'   => 'Use updated_at field as "Last time modified"',
                'comment' => 'Use this page\'s model updated_at field as the "Last time modified"',
                'type'    => 'checkbox',
                'tab'     => 'SEO',
                'span' => 'right'
            ],
            'lastmod' => [
                'label'   => 'Last time modified',
                'type'    => 'datepicker',
                'mode'    => 'datetime',
                'tab'     => 'SEO',
                'span'    => 'left',
                'comment' => 'Date and time this page was last modified'
            ],
            'changefreq' => [
                'label'   => 'Change frequency',
                'type'    => 'dropdown',
                'tab'     => 'SEO',
                'options' => $this->getChangefreqOptions(),
                'span'    => 'left',
                'comment' => 'Tell search engines how frequently this page changes'
            ],
            'priority' => [
                'label'   => 'Priority',
                'type'    => 'dropdown',
                'tab'     => 'SEO',
                'options' => $this->getPriorityOptions(),
                'span'    => 'left',
                'comment' => 'Rank the importance of the page to search engines'
            ],

            'og_title' => [
                'label'   => 'OG Title',
                'tab'     => 'Open Graph',
                'span'    => 'auto',
                'placeholder' => 'Open Graph title'
            ],
            'og_description' => [
                'label'   => 'OG Desciption',
                'tab'     => 'Open Graph',
                'type' => 'textarea',
                'size' => 'tiny',
                'span'    => 'auto',
                'placeholder' => 'Open Graph description'
            ],
            'og_type' => [
                'label'   => 'OG Type',
                'tab'     => 'Open Graph',
                'span'    => 'auto',
                'placeholder' => 'website, article, video, etc...'
            ],
            'og_image' => [
                'label'   => 'OG Image',
                'type' => 'mediafinder',
                'mode' => 'image',
                'tab'     => 'Open Graph',
                'span'    => 'auto',
                'comment' => ''
            ],

            'og_ref_image' => [
                'label'   => 'Dynamic image reference',
                'tab'     => 'Open Graph',
                'span'    => 'auto',
                'placeholder' => '{{ example.image }}',
                'comment' => 'This will take priority over "OG Image"',
            ],
        ];
    }
    private function getChangefreqOptions() {
        return [
            '' => 'default',
            'always' => 'always',
            'hourly' => 'hourly',
            'weekly' => 'weekly',
            'monthly' => 'monthly',
            'yearly' => 'yearly',
            'never' => 'never',
        ];
    }
    private function getPriorityOptions() {
        return [ 
            '' => 'default',
            '0.1' => '0.1',
            '0.2' => '0.2',
            '0.3' => '0.3',
            '0.4' => '0.4',
            '0.5' => '0.5',
            '0.6' => '0.6',
            '0.7' => '0.7',
            '0.8' => '0.8',
            '0.9' => '0.9',
            '1.0' => '1.0',
        ];
    }


}
