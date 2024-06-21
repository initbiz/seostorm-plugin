<?php

namespace Initbiz\SeoStorm\Jobs;

use Cms\Classes\Controller;
use Initbiz\SeoStorm\Models\SitemapItem;

class ParseSite
{
    public $failOnTimeout = false;


    public function fire($job, $data)
    {
        $sitemapItem = SitemapItem::where('loc', $data['url'])->first();

        $controller = new Controller();
        try {
            $url = parse_url($sitemapItem->loc);
            $response = $controller->run($url['path']);
        } catch (\Throwable $th) {
            trace_log('Problem with parsing page ' . $sitemapItem->loc);
            return false;
        }
        $content = $response->getContent();

        $dom = new \DOMDocument();
        $dom->loadHTML($content ?? ' ', LIBXML_NOERROR);

        $sitemapItem->images = $this->getImagesLinksFromDom($dom);

        $sitemapItem->videos = $this->getVideoItemsFromDom($dom);

        $sitemapItem->save();
    }

    public function getImagesLinksFromDom(\DOMDocument $dom): array
    {
        $links = [];

        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//img");
        foreach ($nodes as $node) {
            $link = $node->getAttribute('src');
            if (!blank($link)) {
                $links[] = ['url' => $link];
            }
        }

        return $links;
    }

    protected function getVideoItemsFromDom(\DOMDocument $dom): array
    {
        $items = [];

        $finder = new \DomXPath($dom);
        $schemaName = "https://schema.org/VideoObject";
        $nodes = $finder->query("//*[contains(@itemtype, '$schemaName')]");

        foreach ($nodes as $node) {
            $video = [];
            foreach ($node->childNodes as $childNode) {
                if (!$childNode instanceof \DOMElement) {
                    continue;
                }

                if ($childNode->tagName !== 'meta') {
                    continue;
                }

                $key = $childNode->getAttribute('itemprop');
                $value = $childNode->getAttribute('content');

                $video[$key] = $value;
            }

            $items[] = $video;
        }

        return $items;
    }
}
