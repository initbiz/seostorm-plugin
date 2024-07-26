<?php

declare(strict_types=1);

namespace Initbiz\Seostorm\Models;

use Model;
use Cms\Classes\Page;
use System\Models\SiteDefinition;
use October\Rain\Database\Builder;
use October\Rain\Support\Facades\Site;
use RainLab\Pages\Classes\Page as StaticPage;
use Initbiz\SeoStorm\Sitemap\Resources\PageItem;
use Initbiz\SeoStorm\Sitemap\Resources\Changefreq;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;
use Initbiz\SeoStorm\Sitemap\Resources\SitemapItemsCollection;

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
        'priority' => 'nullable|float',
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
        'siteDefinition' => SiteDefinition::class
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
     * Refresh SitemapItem table records for a CMS page
     *
     * @param Page $page
     * @param SiteDefinition|null $site
     * @param SitemapItemsCollection<PageItem>|null $items
     * @return void
     */
    public static function refreshForCmsPage(
        Page $page,
        ?SiteDefinition $site = null,
        ?SitemapItemsCollection $items = null
    ): void {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        if (is_null($items)) {
            $pagesGenerator = new PagesGenerator($site);
            $items = $pagesGenerator->makeItemsForCmsPage($page);
        }

        foreach ($items as $item) {
            $object = SitemapItem::fromSitemapPageItem($item);
            $object->site_definition_id = $site->id;
            $object->save();
        }
    }

    /**
     * Refresh SitemapItem table record for a single static page
     *
     * @param StaticPage $staticPage
     * @param SiteDefinition|null $site
     * @param PageItem|null $item
     * @return void
     */
    public static function refreshForStaticPage(
        StaticPage $staticPage,
        ?SiteDefinition $site = null,
        ?PageItem $item = null
    ): void {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        if (is_null($item)) {
            $pagesGenerator = new PagesGenerator($site);
            $item = $pagesGenerator->makeItemForStaticPage($staticPage);
        }

        $object = SitemapItem::fromSitemapPageItem($item);
        $object->site_definition_id = $site->id;
        $object->save();
    }

    /**
     * Convert this model instance to PageItem instance
     *
     * @return PageItem
     */
    public function toSitemapPageItem(): PageItem
    {
        $pageItem = new PageItem();

        $pageItem->setLoc($this->loc);
        $pageItem->setBaseFileName($this->base_file_name);

        if (!empty($this->lastmod)) {
            $pageItem->setLastmod($this->lastmod);
        }

        if (!empty($this->changefreq)) {
            $changefreq = Changefreq::tryFrom($this->changefreq);
            $pageItem->setChangefreq($changefreq);
        }

        if (!empty($this->priority)) {
            $pageItem->setPriority($this->priority);
        }

        return $pageItem;
    }

    /**
     * Create model instance using PageItem
     *
     * @param PageItem $pageItem
     * @return SitemapItem
     */
    public static function fromSitemapPageItem(PageItem $pageItem): SitemapItem
    {
        $sitemapItem = SitemapItem::where('loc', $pageItem->getLoc())->first();
        if (!$sitemapItem) {
            $sitemapItem = new SitemapItem();
        }

        $sitemapItem->loc = $pageItem->getLoc();
        $sitemapItem->lastmod = $pageItem->getLastmod();
        $sitemapItem->changefreq = $pageItem->getChangefreq()?->value;
        $sitemapItem->priority = $pageItem->getPriority();
        $sitemapItem->base_file_name = $pageItem->getBaseFileName();

        return $sitemapItem;
    }

    /**
     * Every time when creating collection Eloquent will build this collection
     *
     * @param array $models
     * @return SitemapItemsCollection
     */
    public function newCollection(array $models = []): SitemapItemsCollection
    {
        return new SitemapItemsCollection($models);
    }
}
