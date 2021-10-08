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
}
