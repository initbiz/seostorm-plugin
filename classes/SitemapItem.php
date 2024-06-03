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

    public function makeUrlElement(Sitemap $sitemap)
    {
        if ($sitemap->getUrlsCount() >= Sitemap::MAX_URLS) {
            return false;
        }
        $xml = $sitemap->makeRoot();

        $pageUrl = url($this->loc);

        // $this->urlCount++;

        $url = $xml->createElement('url');
        $pageUrl && $url->appendChild($xml->createElement('loc', $pageUrl));
        $url->appendChild($xml->createElement('lastmod', $this->getLastModified()));

        if ($this->changefreq) {
            $url->appendChild($xml->createElement('changefreq', $this->changefreq));
        }

        if ($this->priority) {
            $url->appendChild($xml->createElement('priority', $this->priority));
        }

        foreach ($this->images as $photoUrl) {
            $photoElement = $url->appendChild($xml->createElement('image:image'));
            $photoElement->appendChild($xml->createElement('image', url($photoUrl)));
        }

        foreach ($this->videos as $video) {
            $photoElement = $url->appendChild($xml->createElement('video:video'));
            $photoElement->appendChild($xml->createElement('video:thumbnail_loc', url($video["thumbnailUrl"])));
            $photoElement->appendChild($xml->createElement('video:title', $video["name"]));
            $photoElement->appendChild($xml->createElement('video:player_loc', htmlspecialchars(url($video["embedUrl"]))));
            $photoElement->appendChild($xml->createElement('video:description', $video["description"]));
        }

        return $url;
    }

    public function getLastModified()
    {
        try {
            $lastmod = new Carbon($this->lastmod);
        } catch (\Throwable $th) {
            $lastmod = new Carbon();
        }

        return $lastmod->format('c');
    }
}
