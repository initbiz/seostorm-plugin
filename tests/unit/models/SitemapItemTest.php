<?php

namespace Initbiz\SeoStorm\Tests\Unit\Models;

use Queue;
use Config;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\ComponentManager;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\Seostorm\Models\SitemapMedia;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;
use Initbiz\SeoStorm\Jobs\ScanPageForMediaItems as ScanPageForMediaItems;
use Initbiz\SeoStorm\Tests\Classes\FakeModelDetailsComponent;

class SitemapItemTest extends StormedTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $componentManager = ComponentManager::instance();
        $componentManager->listComponents();
        $componentManager->registerComponent(FakeModelDetailsComponent::class, 'fakeModelDetails');
    }

    public function testMakeSitemapItemsForCmsPage(): void
    {
        Queue::fake();
        Theme::setActiveTheme('test');
        $theme = Theme::load('test');
        $page = Page::load($theme, 'empty.htm');
        SitemapItem::refreshForCmsPage($page);

        $sitemapItems = SitemapItem::get();

        $this->assertEquals(1, $sitemapItems->count());
    }

    public function testParseSiteImages(): void
    {
        Queue::fake();
        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-media-2.htm');
        SitemapItem::refreshForCmsPage($page);
        $sitemapItem = SitemapItem::first();
        Queue::assertPushed(ScanPageForMediaItems::class);
        (new ScanPageForMediaItems())->scan($sitemapItem->loc);

        $sitemapItem = SitemapItem::with(['videos', 'images'])->first();
        $this->assertEquals(1, $sitemapItem->videos->count());
        $this->assertEquals(1, $sitemapItem->images->count());
        $this->assertEquals('https://test.dev/images2.jpg', $sitemapItem->images->first()->loc);
        $this->assertEquals('image', $sitemapItem->images->first()->type);

        // Re-scan to ensure no new items were added
        (new ScanPageForMediaItems())->scan($sitemapItem->loc);
        $this->assertEquals(2, SitemapMedia::count());

        $page = Page::load($theme, 'with-media-2.htm');
        SitemapItem::refreshForCmsPage($page);
        (new ScanPageForMediaItems())->scan($sitemapItem->loc);

        $this->assertEquals(2, SitemapMedia::count());
    }

    public function testParseSiteVideos(): void
    {
        Queue::fake();
        Theme::setActiveTheme('test');
        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-media-2.htm');
        SitemapItem::refreshForCmsPage($page);
        Queue::assertPushed(ScanPageForMediaItems::class);
        $sitemapItem = SitemapItem::first();

        (new ScanPageForMediaItems())->scan($sitemapItem->loc);

        $sitemapItem = SitemapItem::first();
        $sitemapVideo = $sitemapItem->videos()->first();
        $this->assertEquals('https://player.vimeo.com/video/347119375?autopause=1&badge=0&byline=0&color=57e117&portrait=0&title=0#t=0', $sitemapVideo->loc);
        $this->assertEquals('video', $sitemapVideo->type);
        $this->assertEquals([
            'title' => 'Test title',
            'description' => 'Test description',
            'thumbnail_loc' => 'https://i.vimeocdn.com/video/797382244-0106ae13e902e09d0f02d8f404fa80581f38d1b8b7846b3f8e87ef391ffb8c99-d?mw=1200&amp;mh=675&amp;q=70',
            'publication_date' => '2024-06-26T16:18:25+00:00',
        ], $sitemapVideo->additional_data);
    }

    public function testParseSiteWhenUpdateModel(): void
    {
        Queue::fake();
        Theme::setActiveTheme('test');

        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->created_at = \Carbon\Carbon::parse('today');
        $model->save();

        $sitemapItems = SitemapItem::get();
        $this->assertEquals(1, $sitemapItems->count());
        foreach ($sitemapItems as $sitemapItem) {
            (new ScanPageForMediaItems())->scan($sitemapItem->loc);
        }
    }
}
