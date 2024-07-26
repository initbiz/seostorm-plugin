<?php

namespace Initbiz\Seostorm\Models;

use Model;
use System\Models\SiteDefinition;
use October\Rain\Database\Builder;
use Initbiz\SeoStorm\Sitemap\Resources\PageItem;
use Initbiz\SeoStorm\Sitemap\Resources\Changefreq;
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
        'priority' => 'nullable|float',
        'changefreq' => 'nullable|in:always,hourly,daily,weekly,monthly,yearly,never',
        'lastmod' => 'nullable|date',
    ];

    protected $casts = [
        'priority' => 'float',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
        $sitemapItem->changefreq = $pageItem->getChangefreq()->value;
        $sitemapItem->priority = $pageItem->getPriority();

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
