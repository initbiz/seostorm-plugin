<?php

namespace Initbiz\SeoStorm\Jobs;

use Request;
use Cms\Classes\Controller;
use Initbiz\SeoStorm\Models\SitemapItem;
use Initbiz\Seostorm\Models\SitemapMedia;
use Illuminate\Http\Request as HttpRequest;

class ParseSiteJob
{
    public $failOnTimeout = false;


    public function fire($job, $data)
    {
        $originalRequest = Request::getFacadeRoot();
        $request = new HttpRequest();
        Request::swap($request);
        $sitemapItem = SitemapItem::where('loc', $data['url'])->first();

        $controller = new Controller();
        try {
            $parsedUrl = parse_url($sitemapItem->loc);
            $url = $parsedUrl['path'] ?? '/';
            $response = $controller->run($url);
        } catch (\Throwable $th) {

            trace_log('Problem with parsing page ' . $sitemapItem->loc);
            throw new \Exception($th, 1);
            return false;
        }
        $content = $response->getContent();

        $dom = new \DOMDocument();
        $dom->loadHTML($content ?? ' ', LIBXML_NOERROR);
        $images = $this->getImagesLinksFromDom($dom);
        if (!empty($images)) {
            $imagesIds = [];
            foreach ($images as $image) {
                $sitemapMedia = SitemapMedia::where('url', $image['url'])->first();
                if (!$sitemapMedia) {
                    $sitemapMedia = new SitemapMedia();
                    $sitemapMedia->type = 'image';
                    $sitemapMedia->url = $image['url'];
                    $sitemapMedia->values = $image;
                    $sitemapMedia->save();
                }
                $imagesIds[] = $sitemapMedia->id;
            }
            $sitemapItem->media()->sync($imagesIds);
        }

        $videos = $this->getVideoItemsFromDom($dom);
        if (!empty($videos)) {
            $videosIds = [];
            foreach ($videos as $video) {
                $sitemapMedia = SitemapMedia::where('url', $video["embedUrl"])->first();
                if (!$sitemapMedia) {
                    $sitemapMedia = new SitemapMedia();
                    $sitemapMedia->type = 'video';
                    $sitemapMedia->url = $video["embedUrl"];
                    $sitemapMedia->values = $video;
                    $sitemapMedia->save();
                }

                $videosIds[] = $sitemapMedia->id;
            }

            $sitemapItem->media()->sync($videosIds);
        }

        $sitemapItem->save();

        Request::swap($originalRequest);
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
