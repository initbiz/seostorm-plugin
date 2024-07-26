<?php

namespace Initbiz\Seostorm\Models;

use Model;
use DOMElement;
use Carbon\Carbon;
use System\Models\SiteDefinition;
use October\Rain\Database\Builder;
use Initbiz\SeoStorm\Contracts\Changefreq;
use Initbiz\SeoStorm\Classes\AbstractGenerator;
use Initbiz\SeoStorm\Classes\SitemapItemsCollection;
use Initbiz\SeoStorm\Contracts\SitemapPageItem;

class SitemapItem extends Model implements SitemapPageItem
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
