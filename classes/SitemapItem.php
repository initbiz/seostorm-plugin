<?php

namespace Initbiz\SeoStorm\Classes;

use Carbon\Carbon;

class SitemapItem
{
    public $loc;

    public $lastmod;

    public $priority;

    public $changefreq;

    public array $images = [];

    public array $videos = [];


    public function getLastModified()
    {
        try {
            $lastmod = new Carbon($this->lastmod);
        } catch (\Throwable $th) {
            $lastmod = new Carbon();
        }

        return $lastmod->format('c');
    }

    public static function makeItemForCmsPage($page): self
    {
        $sitemapItem = new SitemapItem();
        $sitemapItem->priority = $page->seoOptionsPriority;
        $sitemapItem->changefreq = $page->seoOptionsChangefreq;
        $sitemapItem->loc = $page->url;
        $sitemapItem->lastmod = $page->lastmod ?: Carbon::createFromTimestamp($page->mtime);

        return $sitemapItem;
    }

    public function toArray(): array
    {
        return [
            'loc' => $this->loc,
            'lastmod' => $this->getLastModified(),
            'priority' => $this->priority,
            'changefreq' => $this->changefreq,
            'images' => $this->images,
            'videos' => $this->videos,
        ];
    }
}
