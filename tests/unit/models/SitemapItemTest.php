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
        (new ScanPageForMediaItems())->parse($sitemapItem->loc);

        $sitemapMedia = SitemapItem::first()->media;
        $this->assertEquals(2, $sitemapMedia->count());
        $this->assertEquals('https://test.dev/images2.jpg', $sitemapMedia->first()->url);
        $this->assertEquals('image', $sitemapMedia->first()->type);

        (new ScanPageForMediaItems())->parse($sitemapItem->loc);

        $sitemapMedia = SitemapItem::first()->media;
        $this->assertEquals(2, $sitemapMedia->count());

        $page = Page::load($theme, 'with-media-2.htm');
        SitemapItem::refreshForCmsPage($page);
        (new ScanPageForMediaItems())->parse($sitemapItem->loc);

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

        (new ScanPageForMediaItems())->parse($sitemapItem->loc);

        $sitemapMedia = SitemapItem::first()->media;
        $this->assertEquals(2, $sitemapMedia->count());
        $sitemapVideo = $sitemapMedia->where('type', 'video')->first();
        $this->assertEquals('https://player.vimeo.com/video/347119375?autopause=1&badge=0&byline=0&color=57e117&portrait=0&title=0#t=0', $sitemapVideo->url);
        $this->assertEquals('video', $sitemapVideo->type);
        $this->assertEquals([
            'name' => 'Test title',
            'description' => 'Test description',
            'thumbnailUrl' => 'https://i.vimeocdn.com/video/797382244-0106ae13e902e09d0f02d8f404fa80581f38d1b8b7846b3f8e87ef391ffb8c99-d?mw=1200&amp;mh=675&amp;q=70',
            'uploadDate' => '2024-06-26T16:18:25+00:00',
            'embedUrl' => 'https://player.vimeo.com/video/347119375?autopause=1&badge=0&byline=0&color=57e117&portrait=0&title=0#t=0'
        ], $sitemapVideo->values);
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
            (new ScanPageForMediaItems())->parse($sitemapItem->loc);
        }
    }
}
