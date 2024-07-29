<?php

namespace Initbiz\SeoStorm\Jobs;

use Request;
use Cms\Classes\CmsController;
use Initbiz\SeoStorm\Models\SitemapItem;
use Illuminate\Http\Request as HttpRequest;
use Initbiz\Sitemap\DOMElements\ImageDOMElement;
use Initbiz\Sitemap\DOMElements\VideoDOMElement;

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

        $images = $this->getImagesFromDOM($dom);
        if (!empty($images)) {
            $sitemapItem->syncImages($images);
        }

        $videos = $this->getVideosFromDOM($dom);
        if (!empty($videos)) {
            $sitemapItem->syncVideos($videos);
        }

        Request::swap($originalRequest);
    }

    /**
     * Get image objects from DOMDocument
     *
     * @param \DOMDocument $dom
     * @return array<ImageDOMElement>
     */
    public function getImagesFromDOM(\DOMDocument $dom): array
    {
        $imageDOMElements = [];

        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//img");
        foreach ($nodes as $node) {
            $src = $node->getAttribute('src');
            if (blank($src)) {
                continue;
            }

            $imageDOMElement = new ImageDOMElement();
            $imageDOMElement->setLoc(url($src));
            $imageDOMElements[] = $imageDOMElement;
        }

        return $imageDOMElements;
    }

    /**
     * Get Video objects from DOMDocument
     * We're taking only videos that have itemtype defined as VideoObject
     *
     * @param \DOMDocument $dom
     * @return array
     */
    protected function getVideosFromDOM(\DOMDocument $dom): array
    {
        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//*[contains(@itemtype, 'https://schema.org/VideoObject')]");

        $videos = [];
        foreach ($nodes as $node) {
            $videoDOMElement = new VideoDOMElement();
            foreach ($node->childNodes as $childNode) {
                if (!$childNode instanceof \DOMElement) {
                    continue;
                }

                if ($childNode->tagName !== 'meta') {
                    continue;
                }

                $propertyName = $childNode->getAttribute('itemprop');
                $methodName = 'set' . studly_case($propertyName);
                if (method_exists($videoDOMElement, $methodName)) {
                    $videoDOMElement->$methodName($childNode->getAttribute('content'));
                }

                if ($propertyName === 'embedUrl') {
                    $videoDOMElement->setPlayerLoc($childNode->getAttribute('content'));
                } elseif ($propertyName === 'uploadDate') {
                    $videoDOMElement->setPublicationDate(new \DateTime($childNode->getAttribute('content')));
                } elseif ($propertyName === 'thumbnailUrl') {
                    $videoDOMElement->setThumbnailLoc($childNode->getAttribute('content'));
                } elseif ($propertyName === 'name') {
                    $videoDOMElement->setTitle($childNode->getAttribute('content'));
                }
            }

            $videos[] = $videoDOMElement;
        }

        return $videos;
    }
}
