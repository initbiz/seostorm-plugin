<?php namespace Arcane\Seo;

use System\Classes\PluginBase;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use System\Classes\SettingsManager;
use Arcane\Seo\Classes\Helper;
use Cms\Classes\Controller;

use Arcane\Seo\Models\Settings;

/**
 * Arcane Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = [
        'VojtaSvoboda.TwigExtensions',
    ];

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
                'permissions' => [ 'arcainz.arcane.manage_seo' ],
            ]
        ];

    }

    public function registerMarkupTags()
    {
        $helper = new Helper();
        return [
            'filters' => [
                'seotitle'    => [$helper, 'generateTitle'],
                'removenulls' => [$helper, 'removeNullsFromArray'],
                'fillparams'  => ['Arcane\Seo\Classes\Helper', 'replaceUrlPlaceholders'],
                't'   => ['Arcane\Seo\Classes\Helper', 'parseIfTwigSyntax'],
                'url' => [$helper, 'url'],
                'd_8601' => [$helper, 'd_8601'],
                'i_8601' => [$helper, 'i_8601'],

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

    public function registeFormWidgets() {
      return [
        '\Arcane\Seo\FormWidgets\SeoCharCounter' => [
            'label' => 'SEO Character Counter',
            'code' => 'seocharcounter',
        ],
      ];
    }

    public function boot() {
        \Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
          $controller->addJs('/plugins/arcane/seo/assets/arcane.seo.js');
        });

        if(PluginManager::instance()->hasPlugin('RainLab.Blog'))
        {
            \RainLab\Blog\Models\Post::extend(function($model) {
                $model->addJsonable('arcane_seo_options');
            });
        }

       \Event::listen('cms.template.processSettingsAfterLoad', function (\Cms\Controllers\Index $controller, $template) {
            // dd($template);
            // assign defaults to the page
            $this->setPageDefaultsTo($template);
        }); 

        \Event::listen('cms.template.processSettingsBeforeSave', function (\Cms\Controllers\Index $controller, $dataHolder) {
            // Make some modifications to the $dataHolder object
            $this->setPageDefaultsTo($dataHolder);
        });
    }

    private function setPageDefaultsTo(&$template) {
        $template->settings = array_merge($template->settings, [
            'robot_index' => $template->settings['robot_index'] ?? 'index',
            'robot_follow' => $template->settings['robot_follow'] ?? 'follow',
            'enabled_in_sitemap'=> $template->settings['enabled_in_sitemap'] ?? 1
        ]);
    }
    

    public function register()
    {
        \Event::listen('backend.form.extendFields', function($widget)
        {
            if ($widget->isNested === false ) {
            
                if (!($theme = Theme::getEditTheme())) throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
                if (
                    PluginManager::instance()->hasPlugin('RainLab.Pages') 
                    && $widget->model instanceof \RainLab\Pages\Classes\Page) {  
                        
                    $widget->removeField('viewBag[meta_title]' );
                    $widget->removeField('viewBag[meta_description]');
                    $widget->addFields( array_except($this->staticSeoFields(), [
                        'viewBag[model_class]', 
                        'viewBag[use_updated_at]'
                    ]), 'primary'); 
                }
            
                if (
                    PluginManager::instance()->hasPlugin('RainLab.Blog') 
                    && $widget->model instanceof \RainLab\Blog\Models\Post) {

                        $widget->addFields( array_except($this->blogSeoFields(), [
                            'arcane_seo_options[model_class]', 
                            'arcane_seo_options[lastmod]', 
                            'arcane_seo_options[use_updated_at]', 
                            'arcane_seo_options[changefreq]', 
                            'arcane_seo_options[priority]'
                        ]), 'secondary');
                }
                
                if (!$widget->model instanceof \Cms\Classes\Page) return;
                
                $widget->removeField('settings[meta_title]');
                $widget->removeField('settings[meta_description]');
                $widget->addFields( $this->cmsSeoFields(), 'primary');

            }

        });
    }

    
    private function blogSeoFields() {
        return collect($this->seoFields())->mapWithKeys(function($item, $key) {
            return ["arcane_seo_options[$key]" => $item];
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
            'meta_title' => [
                'label'   => 'Page title (dynamic)',
                'type'    => 'text',
                'span' => 'full',
                'tab'     => 'Meta',
                'comment' => 'Page title',
                'attributes' => ['data-counter'=>1, 'data-min'=>30,'data-max'=>60, 'data-seo' => 'title']
            ],
            'meta_description' => [
                'label' => 'Page description (dynamic)',
                'type'    => 'textarea',
                'size' => 'tiny',
                'span' => 'full',
                'tab' => 'Meta',
                'cssClass'=> 'char-counter',
                'comment' => 'Page description',
                'attributes' => ['data-counter'=>1,'data-min'=>100,'data-max'=>160, 'data-seo' => 'description']
            ],
            'canonical_url' => [
                'label'   => 'Canonical URL (dynamic)',
                'type'    => 'text',
                'tab'     => 'Meta',
                'span'    => 'full',
                'comment' => 'The canonical URL this page should point to, defaults to current URL'
            ],
            'robot_index' => [
                'label'   => 'robots index',
                'type'    => 'balloon-selector',
                'tab'     => 'Meta',
                'options' => [
                    'index' => 'index',
                    'noindex' => 'noindex'
                ],
                'comment' => 'Specify if search engines should index this page',
                'span'    => 'left',
                'default' => 'index'
            ],
            'robot_follow' => [
                'label'   => 'robots follow',
                'type'    => 'balloon-selector',
                'tab'     => 'Meta',
                'options' => [
                    'follow' => 'follow',
                    'nofollow' => 'nofollow',
                ],
                'comment' => 'Specify if search engines should follow the links on this page',
                'span'    => 'right',
                'default' => 'follow'
            ],
            'robot_advanced' => [
                'label'   => 'Advanced robots (dynamic)',
                'tab'     => 'Meta',
                'comment' => 'Add aditional directives to the robots meta tag, separated by commas',
                'span' => 'left',
            ],
            'enabled_in_sitemap' => [
                'label'   => 'Enable in the sitemap.xml',
                'comment' => 'Page will appear in the sitemap.xml',
                'type'    => 'checkbox',
                'tab'     => 'Sitemap',
                'span' => 'left',
                'default'=> true,
            ],
            'model_class' => [
                'label'   => 'Model class',
                'type'    => 'text',
                'span'    => 'right',
                'comment' => 'Associate this page with a model, links will be generated from it\'s records ',
                'placeholder' => 'Author\Plugin\Models\ModelClass',
                'tab'     => 'Sitemap',
            ],
            'use_updated_at' => [
                'label'   => 'Use "updated_at" from the model as "Last time modified"',
                'commentHtml'=> true,
                'comment' => "If the updated_at field isn't available in the model, it will default to the file's last time modified aka: <b>Page::\$mtime</b>",
                'type'    => 'checkbox',
                'tab'     => 'Sitemap',
                'default' => true,
            ],
            'lastmod' => [
                'label'   => 'Last time modified',
                'type'    => 'datepicker',
                'mode'    => 'datetime',
                'tab'     => 'Sitemap',
                'span'    => 'full',
                'trigger' => [
                    'action'=> 'hide',
                    'field'=> 'settings[use_updated_at]',
                    'condition'=> 'checked',
                ],
                'comment' => 'Date and time this page was last modified',
                
            ],
            'changefreq' => [
                'label'   => 'Changing frequency',
                'type'    => 'dropdown',
                'tab'     => 'Sitemap',
                'options' => $this->getChangefreqOptions(),
                'span'    => 'left',
                'comment' => 'Tell search engines how frequently this page changes'
            ],
            'priority' => [
                'label'   => 'Priority',
                'type'    => 'dropdown',
                'tab'     => 'Sitemap',
                'options' => $this->getPriorityOptions(),
                'span'    => 'right',
                'comment' => 'Rank the importance of the page to search engines'
            ],
            
            'og_title' => [
                'label'   => 'og:title (dynamic)',
                'tab'     => 'Open Graph',
                'span'    => 'auto',
                'comment' => 'Defaults to SEO'
            ],
            'og_description' => [
                'label'   => 'og:description (dynamic)',
                'tab'     => 'Open Graph',
                'type' => 'textarea',
                'size' => 'tiny',
                'span'    => 'auto',
                'comment' => 'Defaults to SEO'
            ],
            'og_type' => [
                'label'   => 'og:type (dynamic)',
                'tab'     => 'Open Graph',
                'span'    => 'auto',
                'placeholder' => 'website, article, video, etc...'
            ],
            'og_image' => [
                'label'   => 'og:image',
                'type' => 'mediafinder',
                'mode' => 'image',
                'tab'     => 'Open Graph',
                'span'    => 'auto',
                'comment' => ''
            ],

            'og_ref_image' => [
                'label'   => 'og:image (dynamic)',
                'tab'     => 'Open Graph',
                'span'    => 'auto',
                'placeholder' => '{{ example.image }}',
                'comment' => 'This will take priority over og:image',
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
