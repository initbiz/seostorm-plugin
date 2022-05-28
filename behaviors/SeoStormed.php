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
    }

    /**
     * Accessor to seo_options attribute which will get the value from
     * the related by seostorm_options morph relation
     *
     * @return array|null
     */

    public function getSeoOptionsAttribute(): ?array
    {
        if ($this->model->seostorm_options) {
            return $this->model->seostorm_options->options;
        }

        return null;
    }

    /**
     * Mutator for the seo_options attribute which will
     * save the value to the related morph
     *
     * @param array $value
     * @return void
     */
    public function setSeoOptionsAttribute($value): void
    {
        $seoOptions = $this->model->seostorm_options()->withDeferred(post('_session_key'))->first();

        if ($seoOptions) {
            $seoOptions->options = $value;
            $this->model->seostorm_options()->add($seoOptions);
        } else {
            /*
             * If the parent model doesn't exist
             * we have to save the child and defer the binding
             */
            $seoOptions = new SeoOptions();
            $seoOptions->options = $value;
            $seoOptions->save();
            $this->model->seostorm_options()->add($seoOptions, post('_session_key'));
        }

        unset($this->model->attributes['seo_options']);
    }
}
