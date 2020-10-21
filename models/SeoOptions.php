<?php

namespace Initbiz\SeoStorm\Models;

use Model;

/**
 * SeoOptions Model
 */
class SeoOptions extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'initbiz_seostorm_seo_options';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = ['options'];

    public $timestamps = false;

    public $morphTo = [];
}
