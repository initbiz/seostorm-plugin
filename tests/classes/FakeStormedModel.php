<?php

namespace Initbiz\SeoStorm\Tests\Classes;

use Model;

class FakeStormedModel extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'initbiz_fake_stormed_model';

    public $timestamps = false;

    protected $guarded = ['*'];
}
