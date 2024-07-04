<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Model;
use Request;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\CmsObject;
use Initbiz\Seostorm\Models\SitemapItem;
use Illuminate\Http\Request as HttpRequest;
use Initbiz\SeoStorm\Classes\SitemapGenerator;
use October\Rain\Halcyon\Model as HalcyonModel;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;

class SitemapHandler
{
    public function subscribe($event)
    {
        $this->afterSaveCmsPage($event);
        $this->afterUpdateModel($event);
    }

    public function afterSaveCmsPage($event): void
    {
        $event->listen('halcyon.saved: RainLab\Pages\Classes\Page', function ($model) {
            SitemapItem::makeSitemapItemsForStaticPage($model);
        });

        $event->listen('halcyon.saved: Cms\Classes\Page', function ($model) {
            SitemapItem::makeSitemapItemsForCmsPage($model);
        });
    }

    public function afterUpdateModel(): void
    {
        $currentTheme = Theme::getActiveTheme();
        $pagesObject = Page::listInTheme($currentTheme, true);
        Model::extend(function ($model) use ($pagesObject) {
            $class = get_class($model);
            $pagesForModel = $pagesObject->where('seoOptionsModelClass', $class);
            $model->bindEvent('model.afterSave', function () use ($model, $pagesForModel) {
                foreach ($pagesForModel as $page) {
                    SitemapItem::makeSitemapItemsForCmsPage($page);
                }
            });
        });
    }
}
