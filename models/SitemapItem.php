<?php

declare(strict_types=1);

namespace Initbiz\Seostorm\Models;

use Event;
use Model;
use Queue;
use Cms\Classes\Page;
use System\Models\SiteDefinition;
use October\Rain\Database\Builder;
use Initbiz\Sitemap\Values\Changefreq;
use October\Rain\Support\Facades\Site;
use RainLab\Pages\Classes\Page as StaticPage;
use Initbiz\Sitemap\DOMElements\UrlDOMElement;
use Initbiz\SeoStorm\Jobs\ScanPageForMediaItems;
use Initbiz\Sitemap\DOMElements\ImageDOMElement;
use Initbiz\Sitemap\DOMElements\VideoDOMElement;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;

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
        'base_file_name' => 'required',
        'priority' => 'nullable|numeric',
        'changefreq' => 'nullable|in:always,hourly,daily,weekly,monthly,yearly,never',
        'lastmod' => 'nullable|date',
    ];

    public $attributes = [
        'is_enabled' => true,
    ];

    protected $casts = [
        'priority' => 'float',
        'is_enabled' => 'bool',
    ];

    protected $dates = [
        'lastmod',
        'created_at',
        'updated_at'
    ];

    public $belongsToMany = [
        'images' => [
            SitemapMedia::class,
            'table' => 'initbiz_seostorm_sitemap_items_media',
            'scope' => 'onlyImages',
        ],

        'videos' => [
            SitemapMedia::class,
            'table' => 'initbiz_seostorm_sitemap_items_media',
            'scope' => 'onlyVideos',
        ],
    ];

    public $belongsTo = [
        'site_definition' => SiteDefinition::class
    ];

    public function scopeWithSite(Builder $query, SiteDefinition $site): Builder
    {
        return $query->where('site_definition_id', $site->id);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function setBaseFileNameAttribute($baseFileName)
    {
        // Remove extension if exists
        if (str_contains($baseFileName, '.')) {
            $baseFileName = substr($baseFileName, 0, (strrpos($baseFileName, ".")));
        }

        $this->attributes['base_file_name'] = $baseFileName;
    }

    /**
     * Sync images attached to the page
     *
     * @param array<ImageDOMElement> $imageDOMElements
     * @return void
     */
    public function syncImages(array $imageDOMElements): void
    {
        $idsToSync = [];
        foreach ($imageDOMElements as $imageDOMElement) {
            $sitemapMedia = SitemapMedia::fromImageDOMElement($imageDOMElement);
            $sitemapMedia->save();
            $idsToSync[] = $sitemapMedia->id;
        }

        // We need to fetch videos IDs to not touch them when syncing
        $videosIds = $this->videos()->get(['initbiz_seostorm_sitemap_media.id as id'])->pluck('id')->toArray();
        $this->images()->sync(array_merge($videosIds, $idsToSync));

        SitemapMedia::deleteGhosts();
    }

    /**
     * Sync videos attached to the page
     *
     * @param array<VideoDOMElement> $videoDOMElements
     * @return void
     */
    public function syncVideos(array $videoDOMElements): void
    {
        $idsToSync = [];
        foreach ($videoDOMElements as $videoDOMElement) {
            $sitemapMedia = SitemapMedia::fromVideoDOMElement($videoDOMElement);
            $sitemapMedia->save();
            $idsToSync[] = $sitemapMedia->id;
        }

        // We need to fetch images IDs to not touch them when syncing
        $imagesIds = $this->images()->get(['initbiz_seostorm_sitemap_media.id as id'])->pluck('id')->toArray();
        $this->videos()->sync(array_merge($imagesIds, $idsToSync));

        SitemapMedia::deleteGhosts();
    }

    /**
     * Refresh SitemapItem table records for a CMS page
     *
     * @param Page $page
     * @param SiteDefinition|null $site
     * @param array<SitemapItem>|null $items the items will be added, if not provided, we'll build them from scratch
     * @return void
     */
    public static function refreshForCmsPage(
        Page $page,
        ?SiteDefinition $site = null,
        ?array $items = null
    ): void {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        if (is_null($items)) {
            $pagesGenerator = new PagesGenerator($site);
            $items = $pagesGenerator->makeItemsForCmsPage($page, $site);
        }

        $idsToLeave = [];
        $baseFileNamesToScan = [];
        foreach ($items as $item) {
            $idsToLeave[] = $item->id;
            $baseFileNamesToScan[] = $item->base_file_name;
            $item->save();
            Queue::push(ScanPageForMediaItems::class, ['loc' => $item->loc]);
        }

        // Remove old records, for example when a model in the parameter was removed
        $ghostSitemapItems = SitemapItem::whereIn('base_file_name', $baseFileNamesToScan)
            ->whereNotIn('id', $idsToLeave)
            ->get();

        foreach ($ghostSitemapItems as $ghostSitemapItem) {
            $ghostSitemapItem->delete();
        }

        Event::fire('initbiz.seostorm.sitemapItemForCmsPageRefreshed', [$page]);
    }

    /**
     * Refresh SitemapItem table record for a single static page
     *
     * @param StaticPage $staticPage
     * @param SiteDefinition|null $site
     * @param SitemapItem|null $item the item will be added, if not provided, we'll build it from scratch
     * @return void
     */
    public static function refreshForStaticPage(
        StaticPage $staticPage,
        ?SiteDefinition $site = null,
        ?SitemapItem $item = null
    ): void {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        if (is_null($item)) {
            $pagesGenerator = new PagesGenerator($site);
            $item = $pagesGenerator->makeItemForStaticPage($staticPage, $site);
        }

        $item->save();

        Queue::push(ScanPageForMediaItems::class, ['loc' => $item->loc]);

        Event::fire('initbiz.seostorm.sitemapItemForStaticPageRefreshed', [$staticPage]);
    }

    /**
     * Convert this model instance to UrlDOMElement instance
     *
     * @return UrlDOMElement
     */
    public function toUrlDOMElement(): UrlDOMElement
    {
        $urlDOMElement = new UrlDOMElement();

        $urlDOMElement->setLoc($this->loc);

        if (!empty($this->lastmod)) {
            $urlDOMElement->setLastmod($this->lastmod);
        }

        if (!empty($this->changefreq)) {
            $changefreq = Changefreq::tryFrom($this->changefreq);
            $urlDOMElement->setChangefreq($changefreq);
        }

        if (!empty($this->priority)) {
            $urlDOMElement->setPriority($this->priority);
        }

        return $urlDOMElement;
    }
}
