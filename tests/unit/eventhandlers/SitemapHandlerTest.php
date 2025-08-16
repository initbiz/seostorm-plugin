<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Tests\Unit\Classes;

use Queue;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Models\SiteDefinition;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\SeoStorm\EventHandlers\SitemapHandler;
use Initbiz\SeoStorm\Jobs\ScanPageForMediaItemsJob;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedCategory;

class SitemapHandlerTest extends StormedTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        PagesGenerator::resetCache();
    }

    public function testSavingModelUpdatesSitemap()
    {
        Queue::fake(ScanPageForMediaItemsJob::class);

        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model-category');
        $sitemapHandler = new SitemapHandler();
        $sitemapHandler->registerEventsInTheme($theme);

        $category = new FakeStormedCategory();
        $category->name = 'Category';
        $category->slug = 'category';
        $category->save();

        $model1 = new FakeStormedModel();
        $model1->name = 'Model 1';
        $model1->slug = 'model-1';
        $model1->category()->add($category);
        $model1->save();

        $site = SiteDefinition::first();
        $pagesGenerator = new PagesGenerator($site);
        $pagesGenerator->refreshForCmsPage($page);

        $sitemapItem = SitemapItem::where('base_file_name', 'with-fake-model-category')->first();
        $this->assertEquals(url('/') . '/model/category/model-1', $sitemapItem->loc);

        $model1->slug = 'model-1a';
        $model1->save();

        $model2 = new FakeStormedModel();
        $model2->name = 'Model 2';
        $model2->slug = 'model-2';

        // Testing deferred binding
        $sessionKey = \Str::random();
        $model2->category()->add($category, $sessionKey);
        $model2->save(['sessionKey' => $sessionKey]);

        $sitemapItems = SitemapItem::where('base_file_name', 'with-fake-model-category')->first()->pluck('loc')->toArray();
        $expectedArray = [
            // The first two were added automatically - we don't test them here, only the latter two makes sense
            url('/') . '/images/model-1a',
            url('/') . '/images/model-2',
            url('/') . '/model/category/model-1a',
            url('/') . '/model/category/model-2',
        ];

        $this->assertEquals($expectedArray, $sitemapItems);
    }
}
