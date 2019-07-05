<?php namespace Arcane\Seo\Widgets;

use Backend\Classes\WidgetBase;

/**
 * Minify Form Widget
 */
class ClearCache extends WidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'arcane_seo_clear_cache';

    /**
     * @inheritDoc
     */
    public function init()
    {
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('clearcache');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars() { }

    /**
     * @inheritDoc
     */
    public function loadAssets() { }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return $value;
    } 

    public function onClearCache() {
        \Storage::deleteDirectory('arcane/seo/minify');
        \Flash::success('Cache cleared');
    }
}
