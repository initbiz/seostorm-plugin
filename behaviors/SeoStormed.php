<?php

namespace Initbiz\SeoStorm\Behaviors;

use System\Classes\ModelBehavior;
use Initbiz\SeoStorm\Models\SeoOptions;

class SeoStormed extends ModelBehavior
{
    protected $requiredProperties = [];

    protected $model;

    public function __construct($model)
    {
        parent::__construct($model);

        $this->model = $model;

        $model->extend(function ($model) {
            if (!isset($model->morphOne)) {
                $model->addDynamicProperty('morphOne');
            }

            $model->morphOne['seostorm_options'] = [
                'Initbiz\SeoStorm\Models\SeoOptions',
                'name' => 'stormed',
                'table' => 'initbiz_seostorm_seo_options'
            ];
        });
    }

    public function getSeoOptionsAttribute()
    {
        if ($this->model->seostorm_options) {
            return $this->model->seostorm_options->options;
        }
    }

    public function setSeoOptionsAttribute($value)
    {
        $seoOptions = $this->model->seostorm_options;
        if (!$seoOptions) {
            $seoOptions = new SeoOptions();
        }
        $seoOptions->options = $value;
        $this->model->seostorm_options()->add($seoOptions);
    }
}
