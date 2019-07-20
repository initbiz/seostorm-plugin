<?php namespace Arcane\Seo\Models;

use Model;

/**
 * Model
 */
class Schema extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'arcane_seo_schemas';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

}
