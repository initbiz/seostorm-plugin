<?php namespace Arcane\Seo\Models;

use Model;

/**
 * StructureData Model
 */
class StructureData extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'arcainz_arcane_structure_datas';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
