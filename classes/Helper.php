<?php namespace Arcane\Seo\Classes;

use Arcane\Seo\Models\Settings;
use Request;
use Carbon\CarbonInterval;
use Carbon\Carbon;

use System\Classes\PluginManager;

class Helper {

    public $settings;

    public function __construct()
    {
        $this->settings = Settings::instance();
    }


    public function generateTitle($title)
    {
        $settings = $this->settings;
        $new_title = "";

        $position = $settings->site_name_position;
        $site_name = $settings->site_name;

        if($position == 'prefix')
        {
            $new_title =   "$site_name {$settings->site_name_separator} $title"  ;
        }
        else if ($position == 'suffix')
        {
            $new_title =  "{$title} {$settings->site_name_separator} {$site_name}"  ;
        } else {
            $new_title = $title;
        }

        return $new_title;
    }

    public function removeNullsFromArray($array) {
        if (! is_array($array) ) throw new \ApplicationException("removenulls can only accept an array as argument" );
            
        return array_filter($array);
    }
    
    public static function replaceUrlPlaceholders($url, $model) {
        if (! is_string($url)) throw new \ApplicationException("Parameter \$url must be a string");
        $params = [];
        preg_match_all('/:(\w+)/', $url, $params, PREG_SET_ORDER);
        $extract =  array_pluck($params, '1', '0'); // ex: [':slug' => 'slug' ]

        $replacedUrl = $url;

        foreach ($extract as $param => $prop) {
            $replacedUrl = str_replace_first($param, $model->$prop, $replacedUrl);
        }

        return $replacedUrl;

    }

    public function url($str) {
        return $str ? url($str) : \Request::url();
    }
    public static function w3cDatetime($date_str)
    {
        return (new Carbon($date_str))->format('c');
    }

    public function d_8601 ($str) {
       return (new Carbon($str))->toIso8601String();
    }

    public Function i_8601 ($str) {
        return CarbonInterval::fromString($str)->spec();
    }

    
    public static function isBlogPost($model) {
        return 
            PluginManager::instance()->hasPlugin('RainLab.Blog') && 
            $model instanceof \RainLab\Blog\Models\Post; 
    }
    public static function isStaticPage($model) {
        return 
            PluginManager::instance()->hasPlugin('RainLab.Pages') && 
            $model instanceof \RainLab\Pages\Classes\Page; 
    }
    public static function isCmsPage($model) {
        return 
            $model instanceof \Cms\Classes\Page; 
    }

    public static function rainlabPagesExists() {
        return PluginManager::instance()->hasPlugin('RainLab.Pages');
    }

    public static function parseAsTwig($str)
    {
        $twigSyntax = $str ? ("{{ {$str} }}") : "{{ null }}";
        return self::parseTwig($twigSyntax, $env);
    }

    public static function  parseIfTwigSyntax($str, $env = null) {
        $str = trim($str);
        
        $isTwig = starts_with($str, "{{") && ends_with($str, '}}');
        
        if (! $isTwig) return $str;
        
        return self::parseTwig($str, $env);
    }
    
    public static function parseTwig($twigString, $env = null) {
        try {
            // dd(\App::make(\Cms\Classes\Controller::class));
            $env = $env ?: \App::make(\Cms\Classes\Controller::class)->vars;

            return (new \October\Rain\Parse\Twig)->parse($twigString, $env );

        } catch (\Exception $ex) {
            // dd($twigString, $env);
            throw new \ApplicationException($ex->getMessage(). " ---> $twigString ");
        }
        
    }
}
