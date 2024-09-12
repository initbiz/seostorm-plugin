<?php

namespace Initbiz\SeoStorm\Tests\Unit\Models;

use Queue;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\ComponentManager;
use System\Models\SiteDefinition;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\Seostorm\Models\SitemapMedia;
use Initbiz\SeoStorm\Jobs\ScanPageForMediaItems;
use Initbiz\SeoStorm\Jobs\UniqueQueueJobDispatcher;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;
use Initbiz\SeoStorm\Tests\Classes\FakeModelDetailsComponent;

class SitemapItemTest extends StormedTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $componentManager = ComponentManager::instance();
        $componentManager->listComponents();
        $componentManager->registerComponent(FakeModelDetailsComponent::class, 'fakeModelDetails');

        $site = new SiteDefinition();
        $site->is_prefixed = false;
        $site->name = 'US';
        $site->code = 'US';
        $site->locale = 'us';
        $site->save();
    }

    public function testMakeSitemapItemsForCmsPage(): void
    {
        Queue::fake();
        Theme::setActiveTheme('test');
        $theme = Theme::load('test');
        $page = Page::load($theme, 'empty.htm');
        $site = SiteDefinition::first();

        $pagesGenerator = new PagesGenerator($site);
        $pagesGenerator->refreshForCmsPage($page);

        $sitemapItems = SitemapItem::get();

        $this->assertEquals(1, $sitemapItems->count());
    }

    public function testParseSiteImages(): void
    {
        Queue::fake();
        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-media-2.htm');

        $settings = Settings::instance();
        $settings->set('enable_images_sitemap', true);
        $settings->set('enable_videos_sitemap', true);

        $site = SiteDefinition::first();
        $pagesGenerator = new PagesGenerator($site);
        $pagesGenerator->refreshForCmsPage($page);
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
        $site = SiteDefinition::first();
        $pagesGenerator = new PagesGenerator($site);
        $pagesGenerator->refreshForCmsPage($page);
        (new ScanPageForMediaItems())->scan($sitemapItem->loc);

        $this->assertEquals(2, SitemapMedia::count());
    }

    public function testParseSiteVideos(): void
    {
        Queue::fake();

        $jobDispatcher = UniqueQueueJobDispatcher::instance();
        $jobDispatcher->resetCache();

        Theme::setActiveTheme('test');
        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-media-2.htm');
        $site = SiteDefinition::first();
        $pagesGenerator = new PagesGenerator($site);
        $pagesGenerator->refreshForCmsPage($page);
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
        $settings = Settings::instance();
        $settings->set('enable_images_sitemap', true);
        $settings->set('enable_videos_sitemap', true);
        Queue::fake();
        Theme::setActiveTheme('test');

        $sitemapItems = SitemapItem::get();
        $this->assertEquals(0, $sitemapItems->count());
        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->description = '<img src="https://test.dev/images3.jpg" alt="" srcset="">';
        $model->created_at = \Carbon\Carbon::parse('today');
        $model->save();

        $sitemapItems = SitemapItem::get();
        $this->assertEquals(1, $sitemapItems->count());
        foreach ($sitemapItems as $sitemapItem) {
            (new ScanPageForMediaItems())->scan($sitemapItem->loc);
        }

        $sitemapMedia = SitemapMedia::all();
        $this->assertEquals(3, $sitemapMedia->count());
        $model->description = '';
        $model->save();

        $sitemapItems = SitemapItem::get();
        foreach ($sitemapItems as $sitemapItem) {
            (new ScanPageForMediaItems())->scan($sitemapItem->loc);
        }

        $sitemapMedia = SitemapMedia::all();
        $this->assertEquals(2, $sitemapMedia->count());

        $model->description = '
        <div class="video-embed"
     itemprop="subjectOf"
     itemscope
     itemtype="https://schema.org/VideoObject">

    <meta itemprop="name"
          content="Test title">
    <meta itemprop="description"
          content="Test description">
    <meta itemprop="thumbnailUrl"
          content="https://i.vimeocdn.com/video/797382244-0106ae13e902e09d0f02d8f404fa80581f38d1b8b7846b3f8e87ef391ffb8c99-d?mw=1200&amp;amp;mh=675&amp;amp;q=70">
    <meta itemprop="uploadDate"
          content="2024-06-26T16:18:25+00:00">
    <meta itemprop="embedUrl"
          content="https://player.vimeo.com/video/347119375?autopause=1&amp;badge=0&amp;byline=0&amp;color=57e117&amp;portrait=0&amp;title=0#t=0">

    <iframe src=https://player.vimeo.com/video/347119375?autopause=1&amp;badge=0&amp;byline=0&amp;color=57e117&amp;portrait=0&amp;title=0#t=0
            frameborder="0"
            allow="autoplay; fullscreen; picture-in-picture"
            allowfullscreen></iframe>

    <script src="https://player.vimeo.com/api/player.js"></script>
</div>';
        $model->save();

        $sitemapItems = SitemapItem::get();
        foreach ($sitemapItems as $sitemapItem) {
            (new ScanPageForMediaItems())->scan($sitemapItem->loc);
        }

        $sitemapMedia = SitemapMedia::all();
        $this->assertEquals(3, $sitemapMedia->count());

        $model->description = '';
        $model->save();

        $sitemapItems = SitemapItem::get();
        foreach ($sitemapItems as $sitemapItem) {
            (new ScanPageForMediaItems())->scan($sitemapItem->loc);
        }

        $sitemapMedia = SitemapMedia::all();
        $this->assertEquals(2, $sitemapMedia->count());
    }
}
