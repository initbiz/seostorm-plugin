<?php

namespace Initbiz\SeoStorm\FormWidgets;

use Lang;
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
    public function render()
    {
        return $this->makePartial('clearcache');
    }

    public function onClearCache()
    {
        Storage::deleteDirectory('initbiz/seostorm/minify');
        Flash::success(Lang::get('initbiz.seostorm::lang.form_widgets.cache_cleared'));
    }
}
