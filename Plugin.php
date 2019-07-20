<?php namespace Arcane\Seo;
use System\Classes\PluginBase;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\SettingsManager;

use System\Classes\PluginManager;
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
                'permissions' => [ 'arcane.manage_seo' ],
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
                'minifycss'=> [$minifier, 'minifyCss'],
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

    public function registerFormWidgets() {
      return [ ];
    }

    public function boot() { }


    public function register()
    {
        \Event::listen('backend.form.extendFields', function($widget)
        {
            if ($widget->isNested === false ) {
            
                if (!($theme = Theme::getEditTheme())) 
                    throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
                    
                if ( PluginManager::instance()->hasPlugin('RainLab.Pages') 
                    && $widget->model instanceof \RainLab\Pages\Classes\Page) {  
                        
                    $widget->removeField('viewBag[meta_title]' );
                    $widget->removeField('viewBag[meta_description]');
                    $widget->addFields( array_except($this->staticSeoFields(), [
                        'viewBag[model_class]', 
                    ]), 'primary'); 
                }
            
                if ( PluginManager::instance()->hasPlugin('RainLab.Blog') 
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
        $user = \BackendAuth::getUser();
        // remove form fields when current users doesn't have access
        return array_except(
            \Yaml::parseFile(plugins_path('arcane/seo/config/seofields.yaml')), 
            array_merge(
                [],
                !$user->hasPermission([ "arcane.seo.og" ]) ? [
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


}
