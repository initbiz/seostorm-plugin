<?php

namespace Initbiz\Seostorm\Models;

use Model;
use October\Rain\Database\Builder;

/**
 * SitemapMedia Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class SitemapMedia extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table name
     */
    public $table = 'initbiz_seostorm_sitemap_media';

    /**
     * @var array rules for validation
     */
    public $rules = [];

    public $jsonable = [
        'values'
    ];

    public $belongsToMany = [
        'items' => [
            SitemapItem::class,
            'table' => 'initbiz_seostorm_sitemap_items_media'
        ]
    ];

    public function scopeOnlyImages(Builder $query): Builder
    {
        return $query->where('type', 'image');
    }

    public function scopeOnlyVideos(Builder $query): Builder
    {
        return $query->where('type', 'video');
    }
}