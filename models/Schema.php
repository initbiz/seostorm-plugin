<?php

namespace Initbiz\SeoStorm\Models;

use Model;

class Schema extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'initbiz_seostorm_schemas';

    /**
     * @var array Validation rules
     */
    public $rules = [];
}
