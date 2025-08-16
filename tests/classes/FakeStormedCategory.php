<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Tests\Classes;

use Model;

class FakeStormedCategory extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'initbiz_fake_stormed_categories';

    protected $guarded = ['*'];

    public $hasMany = [
        'models' => FakeStormedModel::class
    ];
}
