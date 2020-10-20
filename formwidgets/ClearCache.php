<?php

namespace Initbiz\Seo\FormWidgets;

use Flash;
use Storage;
use Backend\Classes\FormWidgetBase;

/**
 * Minify Form Widget
 */
class ClearCache extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'initbiz_seo_clear_cache';

    /**
     * @inheritDoc
     */
    public function render()
    {
        return $this->makePartial('clearcache');
    }

    public function onClearCache()
    {
        Storage::deleteDirectory('initbiz/seo/minify');
        Flash::success('Cache cleared');
    }
}
