<?php

namespace Initbiz\SeoStorm\Classes;

use Request;
use October\Rain\Exception\ApplicationException;

// TODO: the class is to be removed soon
class Helper
{
    public function removeNullsFromArray($array)
    {
        if (!is_array($array)) throw new ApplicationException("removenulls can only accept an array as argument");

        return array_filter($array);
    }

    public static function replaceUrlPlaceholders($url, $model)
    {
        if (!is_string($url)) throw new ApplicationException("Parameter \$url must be a string");
        $params = [];
        preg_match_all('/:(\w+)/', $url, $params, PREG_SET_ORDER);
        $extract =  array_pluck($params, '1', '0'); // ex: [':slug' => 'slug' ]

        $replacedUrl = $url;

        foreach ($extract as $param => $prop) {
            $replacedUrl = str_replace_first($param, $model->$prop, $replacedUrl);
        }

        return $replacedUrl;
    }

    public function url($str)
    {
        return $str ? url($str) : \Request::url();
    }

    public static function parseIfTwigSyntax($str, $env = null)
    {
        $str = trim($str);

        $isTwig = starts_with($str, "{{") && ends_with($str, '}}');

        if (!$isTwig) return $str;

        return self::parseTwig($str, $env);
    }

    public static function parseTwig($twigString, $env = null)
    {
        try {
            $env = $env ?: \App::make(\Cms\Classes\Controller::class)->vars;
            return (new \October\Rain\Parse\Twig)->parse($twigString, $env);
        } catch (\Exception $ex) {
            throw new ApplicationException($ex->getMessage() . " ---> $twigString ");
        }
    }
}
