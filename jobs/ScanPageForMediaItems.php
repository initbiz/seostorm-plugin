<?php

namespace Initbiz\SeoStorm\Jobs;

use Request;
use Cms\Classes\CmsController;
use Initbiz\SeoStorm\Models\SitemapItem;
use Initbiz\Seostorm\Models\SitemapMedia;
use Illuminate\Http\Request as HttpRequest;

class ScanPageForMediaItems
{
    public $failOnTimeout = false;

    public function fire($job, $data)
    {
        $this->scan($data['loc']);
        $job->delete();
    }

    public function scan($loc): void
    {
        $sitemapItem = SitemapItem::where('loc', $loc)->first();
        if (!$sitemapItem) {
            return;
        }

        // We need to temporarily replace request with faked one to get valid URLs
        $originalRequest = Request::getFacadeRoot();
        $request = new HttpRequest();
        Request::swap($request);

        $controller = new CmsController();
        try {
            $parsedUrl = parse_url($loc);
            $url = $parsedUrl['path'] ?? '/';
            $response = $controller->run($url);
        } catch (\Throwable $th) {
            Request::swap($originalRequest);
            trace_log('Problem with parsing page ' . $loc);
            // In case of any issue in the page, we need to ignore it and proceed
            return;
        }

        if ($response->getStatusCode() !== 200) {
            return;
        }

        $content = $response->getContent();

        $dom = new \DOMDocument();
        $dom->loadHTML($content ?? ' ', LIBXML_NOERROR);
        $imagesLocs = $this->getImagesLinksFromDom($dom);
        $sitemapItem->syncImagesUsingLocs($imagesLocs);

        $mediaIds = [];

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

    /**
     * Get image objects from DOMDocument
     *
     * @param \DOMDocument $dom
     * @return array
     */
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

    /**
     * Get Video objects from DOMDocument
     * We're taking only videos that have itemtype defined as VideoObject
     *
     * @param \DOMDocument $dom
     * @return array
     */
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
