<?php

namespace Initbiz\SeoStorm\FormWidgets;

use Lang;
use Flash;
use Redirect;
use Backend\Classes\FormWidgetBase;
use Initbiz\SeoStorm\Classes\Migrator;

/**
 * Migrate from Arcane.SEO Form Widget
 */
class Migrate extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        return $this->makePartial('migrate');
    }

    public function onMigrate()
    {
        Migrator::migrate();
        Flash::success(Lang::get('initbiz.seostorm::lang.form_widgets.successfully_migrated'));
        return Redirect::refresh();
    }
}
