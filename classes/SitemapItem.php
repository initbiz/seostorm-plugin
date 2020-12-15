<?php

namespace Initbiz\SeoStorm\Classes;

use Carbon\Carbon;

class SitemapItem
{
    public $loc;

    public $lastmod;

    public $priority;

    public $changefreq;

    function __construct($url = null, $lastmod = null, $priority = null, $changefreq = null)
    {
        $this->loc = $url;
        $this->lastmod = $lastmod;
        $this->priority = $priority;
        $this->changefreq = $changefreq;
    }

    public static function asStaticPage($staticPage)
    {
        $sitemapItem = new Self();

        $viewBag = $staticPage->getViewBag();

        $sitemapItem->loc = url($staticPage->url);
        $sitemapItem->lastmod = $viewBag->property('lastmod') ?: $staticPage->updated_at;
        $sitemapItem->priority = $viewBag->property('priority');
        $sitemapItem->changefreq = $viewBag->property('changefreq');

        return $sitemapItem;
    }

    public static function asCmsPage($page, $model = null)
    {
        if ($model) {
            return new Self(
                url(Helper::replaceUrlPlaceholders($page->url, $model)),
                $model->updated_at,
                $page->priority,
                $page->changefreq
            );
        }
        return new Self(
            $page->url,
            $page->lastmod ?: Carbon::createFromTimestamp($page->mtime),
            $page->priority,
            $page->changefreq
        );
    }

    public static function asPost($page, $post)
    {
        if ($post) {
            $parts = explode('/', trim($page->url, '/'));
            if (in_array(':category', $parts)) {
                // category in URL, fetch first category slug
                if (isset($post->categories[0])) {
                    $post->category = $post->categories[0]->slug;
                } else {
                    $post->category = 'default';
                }
            }
        }

        $item = new Self;
        $use_updated = $page->use_updated_at;
        $item->loc = url(Helper::replaceUrlPlaceholders($page->url, $post));
        $item->lastmod = $use_updated ? $post->updated_at->format('c') : $page->lastmod;
        $item->priority = $page->priority;
        $item->changefreq = $page->changefreq;

        return $item;
    }
}
