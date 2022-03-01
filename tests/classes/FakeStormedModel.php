<?php

namespace Initbiz\SeoStorm\Tests\Classes;

use Model;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedCategory;

class FakeStormedModel extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'initbiz_fake_stormed_models';

    protected $guarded = ['*'];

    public $belongsTo = [
        'category' => FakeStormedCategory::class
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIsPublished($query, $when)
    {
        $time = \Carbon\Carbon::parse($when);

        return $query->where('created_at', '>', $time);
    }
}
