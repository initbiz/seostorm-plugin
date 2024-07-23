<?php

namespace Initbiz\SeoStorm\Jobs;

use Site;
use Request;
use Cms\Classes\Controller;
use Cms\Classes\CmsController;
use Initbiz\SeoStorm\Models\SitemapItem;
use Initbiz\Seostorm\Models\SitemapMedia;
use Illuminate\Http\Request as HttpRequest;

class ParseSiteJob
{
    public $failOnTimeout = false;

    public function fire($job, $data)
    {
        $this->parse($data['url']);
        $job->delete();
    }

    public function parse($loc): void
    {
        $originalRequest = Request::getFacadeRoot();
        $request = new HttpRequest();
        Request::swap($request);
        $sitemapItem = SitemapItem::where('loc', $loc)->first();

        $controller = new CmsController();
        try {
            $parsedUrl = parse_url($sitemapItem->loc);
            $url = $parsedUrl['path'] ?? '/';
            $response = $controller->run($url);
        } catch (\Throwable $th) {
            Request::swap($originalRequest);
            trace_log('Problem with parsing page ' . $sitemapItem->loc);
            return;
        }
        if ($response->getStatusCode() != 200) {
            return;
        }

        $content = $response->getContent();

        $dom = new \DOMDocument();
        $dom->loadHTML($content ?? ' ', LIBXML_NOERROR);
        $images = $this->getImagesLinksFromDom($dom);
        $mediaIds = [];
        if (!empty($images)) {
            foreach ($images as $image) {
                $sitemapMedia = SitemapMedia::where('url', $image['url'])->first();
                if (!$sitemapMedia) {
                    $sitemapMedia = new SitemapMedia();
                    $sitemapMedia->type = 'image';
                    $sitemapMedia->url = $image['url'];
                    $sitemapMedia->values = $image;
                    $sitemapMedia->save();
                }
                $mediaIds[] = $sitemapMedia->id;
            }
        }

        $videos = $this->getVideoItemsFromDom($dom);
        if (!empty($videos)) {
            foreach ($videos as $video) {
                $sitemapMedia = SitemapMedia::where('url', $video["embedUrl"])->first();
                if (!$sitemapMedia) {
                    $sitemapMedia = new SitemapMedia();
                    $sitemapMedia->type = 'video';
                    $sitemapMedia->url = $video["embedUrl"];
                    $sitemapMedia->values = $video;
                    $sitemapMedia->save();
                }

                $mediaIds[] = $sitemapMedia->id;
            }
        }

        $sitemapItem->media()->sync($mediaIds);
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
