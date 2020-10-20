<?php

namespace Initbiz\SeoStorm\Classes;

use Carbon\Carbon;

class SitemapItem
{
    public $loc, $lastmod, $priority, $changefreq;

    function __construct($url = null, $lastmod = null, $priority = null, $changefreq = null)
    {
        $this->loc = $url;
        $this->lastmod = $lastmod;
        $this->priority = $priority;
        $this->changefreq = $changefreq;
    }

    public static function asStaticPage($staticPage)
    {
        return new self(
            url($staticPage->url),
            $staticPage->getViewBag()->property('lastmod') ?: $staticPage->updated_at,
            $staticPage->getViewBag()->property('priority'),
            $staticPage->getViewBag()->property('changefreq')
        );
    }

    public static function asCmsPage($page, $model = null)
    {
        if ($model)
            return new self(
                url(Helper::replaceUrlPlaceholders($page->url, $model)),
                $model->updated_at,
                $page->priority,
                $page->changefreq
            );
        return new Self(
            $page->url,
            $page->lastmod ?: Carbon::createFromTimestamp($page->mtime),
            $page->priority,
            $page->changefreq
        );
    }

    public static function asPost($page, $post)
    {
        $item = new self;
        $use_updated = $page->use_updated_at;
        $item->loc = url(Helper::replaceUrlPlaceholders($page->url, $post));
        $item->lastmod = $use_updated ? $post->updated_at->format('c') : $page->lastmod;
        $item->priority = $page->priority;
        $item->changefreq = $page->changefreq;

        return $item;
    }
}
