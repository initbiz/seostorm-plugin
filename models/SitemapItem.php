<?php

namespace Initbiz\Seostorm\Models;

use Model;
use Cms\Classes\Controller;
use Initbiz\SeoStorm\Jobs\ParseSite;
use Illuminate\Support\Facades\Queue;
use RainLab\Pages\Classes\Page as StaticPage;
use Initbiz\SeoStorm\Classes\SitemapGenerator;

class SitemapItem extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table name
     */
    public $table = 'initbiz_seostorm_sitemap_items';

    /**
     * @var array rules for validation
     */
    public $rules = [
        'loc' => 'required',
        'images',
        'videos',
        'base_file_name'
    ];

    public $jsonable = [
        'images',
        'videos'
    ];

    public function parsePage(): void
    {
        if (!$this->parseImages && !$this->parseVideos) {
            return;
        }

        $controller = new Controller();
        try {
            $url = parse_url($this->loc);
            $response = $controller->run($url['path']);
        } catch (\Throwable $th) {
            trace_log('Problem with parsing page ' . $this->loc);
            return;
        }
        $content = $response->getContent();

        $dom = new \DOMDocument();
        $dom->loadHTML($content ?? ' ', LIBXML_NOERROR);

        $this->images = $this->getImagesLinksFromDom($dom);

        $this->videos = $this->getVideoItemsFromDom($dom);
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

    protected function getImagesLinksFromDom(\DOMDocument $dom): array
    {
        $links = [];

        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//img");
        foreach ($nodes as $node) {
            $link = $node->getAttribute('src');
            if (!blank($link)) {
                $links[] = $link;
            }
        }

        return $links;
    }

    public static function makeSitemapItemsForCmsPage($page): void
    {
        $sitemapGenerator = new SitemapGenerator();
        $pages = $sitemapGenerator->makeItemsCmsPage($page);
        $sitemapItemModels = self::get();
        foreach ($pages as $sitemapItem) {
            $sitemapItemModel = $sitemapItemModels->where('loc', $sitemapItem['loc'])->first();
            if ($sitemapItemModel) {
                $sitemapItemModel->save();
                continue;
            }
            $sitemapItemModel = new self();
            $sitemapItemModel->loc = $sitemapItem['loc'];
            $sitemapItemModel->base_file_name = $page['base_file_name'];
            $sitemapItemModel->save();
            $sitemapItemModel->queueParseSite();
        }
    }
    public static function makeSitemapItemsForStaticPage($page): void
    {
        $sitemapItemModel = new self();
        $sitemapItemModel->loc = StaticPage::url($page->fileName);
        $sitemapItemModel->base_file_name = $page->fileName;
        $sitemapItemModel->save();
        $sitemapItemModel->queueParseSite();
    }

    public function queueParseSite(): void
    {
        Queue::push(ParseSite::class, ['url' => $this->loc]);
    }
}
