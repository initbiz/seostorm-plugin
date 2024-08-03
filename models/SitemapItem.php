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
