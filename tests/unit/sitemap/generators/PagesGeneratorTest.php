<?php

namespace Initbiz\SeoStorm\Tests\Unit\Classes;

use Queue;
use Config;
use Carbon\Carbon;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use System\Models\SiteDefinition;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\Seostorm\Models\SitemapItem;
use RainLab\Pages\Classes\Page as StaticPage;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedCategory;

class PagesGeneratorTest extends StormedTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $themesPath = plugins_path('initbiz/seostorm/tests/themes');
        Config::set('system.themes_path', $themesPath);
        app()->useThemesPath($themesPath);

        PagesGenerator::resetCache();
        SitemapItem::truncate();
    }

    public function testEnabledInSitemap()
    {
        (PluginManager::instance())->disablePlugin('RainLab.Pages');
        $theme = Theme::load('test');
        $page1 = Page::load($theme, 'empty');
        $page1->mtime = 1632857872;
        $page2 = Page::load($theme, 'with-fake-model');
        $page2->mtime = 1632858273;
        $pages = collect([$page1, $page2]);

        $site = SiteDefinition::first();
        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-1-page.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);

        SitemapItem::truncate();
        PagesGenerator::resetCache();

        $page1->settings['seoOptionsEnabledInSitemap'] = "true";
        $pages = collect([$page1, $page2]);

        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-2-pages.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testHasModelClass()
    {
        Queue::fake();

        (PluginManager::instance())->disablePlugin('RainLab.Pages');
        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model');
        $page->mtime = 1632858273;
        $page->settings['seoOptionsEnabledInSitemap'] = "true";
        $page->settings['seoOptionsModelClass'] = "\Initbiz\SeoStorm\Tests\Classes\FakeStormedModel";
        $page->settings['seoOptionsModelParams'] = "slug:slug";
        $pages = collect();
        $pages = $pages->push($page);

        // Test if sitemap has two elements basing on the models' slugs

        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->save();

        $model2 = new FakeStormedModel();
        $model2->name = 'test-name-2';
        $model2->slug = 'test-slug-2';
        $model2->save();

        $site = SiteDefinition::first();
        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-slugs.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);

        // Test if sitemap has filtered record using the active scope

        $model2->is_active = false;
        $model2->save();

        foreach (SitemapItem::all() as $sitemapItem) {
            $sitemapItem->delete();
        }

        PagesGenerator::resetCache();

        $page->settings['seoOptionsModelScope'] = "active";
        $pages = collect();
        $pages = $pages->push($page);

        $site = SiteDefinition::first();
        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-slugs-filtered.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testParamsWithRelation()
    {
        (PluginManager::instance())->disablePlugin('RainLab.Pages');
        Queue::fake();

        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model-category');
        $page->mtime = 1632858273;
        $page->settings['seoOptionsModelClass'] = "\Initbiz\SeoStorm\Tests\Classes\FakeStormedModel";
        $page->settings['seoOptionsModelParams'] = "slug:slug|category:category.slug";
        $pages = collect();
        $pages = $pages->push($page);

        // Test if sitemap has two elements basing on the models' slugs

        $category = new FakeStormedCategory();
        $category->name = 'cat-test-name';
        $category->slug = 'cat-test-slug';
        $category->save();

        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->category_id = $category->id;
        $model->save();

        $site = SiteDefinition::first();
        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-slugs-relation.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testUseUpdatedAt()
    {
        Queue::fake();

        (PluginManager::instance())->disablePlugin('RainLab.Pages');
        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model');
        $page->mtime = 1632858273;
        $page->settings['seo_options_enabled_in_sitemap'] = "true";
        $page->settings['seoOptionsModelClass'] = "\Initbiz\SeoStorm\Tests\Classes\FakeStormedModel";
        $page->settings['seoOptionsModelParams'] = "slug:slug";
        $page->settings['seoOptionsUseUpdatedAt'] = "true";
        $pages = collect();
        $pages = $pages->push($page);

        // Test if sitemap has two elements basing on the models' slugs

        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->updated_at = Carbon::parse('2021-09-21 10:00');
        $model->save();

        $site = SiteDefinition::first();
        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-updated-at.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testDisabledInModel()
    {
        Queue::fake();

        (PluginManager::instance())->disablePlugin('RainLab.Pages');
        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model');
        $page->mtime = 1632858273;
        $page->settings['seoOptionsEnabledInSitemap'] = "true";
        $page->settings['seoOptionsModelClass'] = "\Initbiz\SeoStorm\Tests\Classes\FakeStormedModel";
        $page->settings['seoOptionsModelParams'] = "slug:slug";
        $pages = collect();
        $pages = $pages->push($page);

        // Test if sitemap has two elements basing on the models' slugs

        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->save();

        $model2 = new FakeStormedModel();
        $model2->name = 'test-name-2';
        $model2->slug = 'test-slug-2';
        $model2->save();

        $model2->seo_options = [
            'enabled_in_sitemap' => "0",
        ];

        $site = SiteDefinition::first();
        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-slugs-filtered.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testOptionalParameterEmpty()
    {
        Queue::fake();

        (PluginManager::instance())->disablePlugin('RainLab.Pages');
        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->save();

        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model-category');
        $page->mtime = 1632858273;
        $page->settings['seoOptionsModelClass'] = "\Initbiz\SeoStorm\Tests\Classes\FakeStormedModel";
        $page->settings['seoOptionsModelParams'] = "category:slug";
        $pages = collect();
        $pages = $pages->push($page);
        $site = SiteDefinition::first();
        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-empty-optional-param.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);

        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model-optional');
        $page->mtime = 1632858273;
        $pages = collect();
        $pages = $pages->push($page);
        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-empty-optional-param-2.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testOptionalScopeParameter()
    {
        Queue::fake();

        (PluginManager::instance())->disablePlugin('RainLab.Pages');
        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->created_at = \Carbon\Carbon::parse('today');
        $model->save();

        $model = new FakeStormedModel();
        $model->name = 'test-name2';
        $model->slug = 'test-slug2';
        $model->created_at = \Carbon\Carbon::parse('before yesterday');
        $model->save();

        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model');
        $page->mtime = 1632858273;
        $page->settings['seoOptionsModelClass'] = "\Initbiz\SeoStorm\Tests\Classes\FakeStormedModel";
        $page->settings['seoOptionsModelParams'] = "slug:slug";
        $page->settings['seoOptionsModelScope'] = "isPublished:yesterday";
        $pages = collect();
        $pages = $pages->push($page);
        $site = SiteDefinition::first();
        $xml = (new PagesGenerator($site))->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-empty-optional-param.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testMakeUrlPattern(): void
    {
        Queue::fake();

        (PluginManager::instance())->disablePlugin('RainLab.Pages');
        $theme = Theme::load('test');
        $site = SiteDefinition::first();
        $pagesGenerator = new PagesGenerator($site);

        $page = Page::load($theme, 'with-fake-model-optional');
        $urlPattern = $pagesGenerator->makeUrlPattern($page);
        $urlPattern = str_replace(url('/'), 'http://initwebsite.devt', $urlPattern);
        $this->assertEquals('http://initwebsite.devt/model/:slug?', $urlPattern);

        $page = Page::load($theme, 'with-fake-model');
        $urlPattern = $pagesGenerator->makeUrlPattern($page);
        $urlPattern = str_replace(url('/'), 'http://initwebsite.devt', $urlPattern);
        $this->assertEquals('http://initwebsite.devt/model/:slug', $urlPattern);

        $page = Page::load($theme, 'with-fake-model-category');
        $urlPattern = $pagesGenerator->makeUrlPattern($page);
        $urlPattern = str_replace(url('/'), 'http://initwebsite.devt', $urlPattern);
        $this->assertEquals('http://initwebsite.devt/model/:category/:slug?', $urlPattern);

        $page = Page::load($theme, 'empty');
        $urlPattern = $pagesGenerator->makeUrlPattern($page);
        $urlPattern = str_replace(url('/'), 'http://initwebsite.devt', $urlPattern);
        $this->assertEquals('http://initwebsite.devt/', $urlPattern);

        $plSite = new SiteDefinition();
        $plSite->is_prefixed = true;
        $plSite->name = 'Polish';
        $plSite->code = 'pl';
        $plSite->route_prefix = '/pl';
        $plSite->locale = 'pl';
        $plSite->save();

        $page = Page::load($theme, 'empty');

        $pagesGenerator = new PagesGenerator($plSite);
        $urlPattern = $pagesGenerator->makeUrlPattern($page);
        $urlPattern = str_replace(url('/'), 'http://initwebsite.devt', $urlPattern);
        $this->assertEquals('http://initwebsite.devt/pl/', $urlPattern);
    }

    public function testFillUrlPatternWithParams(): void
    {
        Queue::fake();

        (PluginManager::instance())->disablePlugin('RainLab.Pages');

        $category = new FakeStormedCategory();
        $category->name = 'cat-test-name';
        $category->slug = 'cat-test-slug';
        $category->save();

        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->category_id = $category->id;
        $model->save();

        $theme = Theme::load('test');
        $site = SiteDefinition::first();
        $pagesGenerator = new PagesGenerator($site);

        $page = Page::load($theme, 'with-fake-model-optional');
        $urlPattern = $pagesGenerator->makeUrlPattern($page);
        $url = $pagesGenerator->fillUrlPatternWithParams($urlPattern, ['slug' => '']);
        $url = str_replace(url('/'), 'http://initwebsite.devt', $url);
        $this->assertEquals('http://initwebsite.devt/model', $url);

        $page = Page::load($theme, 'with-fake-model');
        $urlPattern = $pagesGenerator->makeUrlPattern($page);
        $url = $pagesGenerator->fillUrlPatternWithParams($urlPattern, []);
        $url = str_replace(url('/'), 'http://initwebsite.devt', $url);
        $this->assertEquals('http://initwebsite.devt/model/default', $url);

        $page = Page::load($theme, 'with-fake-model-category');
        $urlPattern = $pagesGenerator->makeUrlPattern($page);
        $params = $pagesGenerator->generateParamsToUrl('slug:slug|category:category.slug', $model);
        $url = $pagesGenerator->fillUrlPatternWithParams($urlPattern, $params);
        $url = str_replace(url('/'), 'http://initwebsite.devt', $url);
        $this->assertEquals('http://initwebsite.devt/model/cat-test-slug/test-slug', $url);

        $page = Page::load($theme, 'empty');
        $urlPattern = $pagesGenerator->makeUrlPattern($page);
        $url = str_replace(url('/'), 'http://initwebsite.devt', $urlPattern);
        $this->assertEquals('http://initwebsite.devt/', $url);

        $urlPattern = 'http://initwebsite.devt/:slug/:slug1/:slug2?';
        $params = [
            'slug' => 'test-1',
            'slug2' => 'test-3',
            'slug1' => 'test-2',
        ];
        $url = $pagesGenerator->fillUrlPatternWithParams($urlPattern, $params);
        $this->assertEquals('http://initwebsite.devt/test-1/test-2/test-3', $url);
    }

    public function testStaticPages(): void
    {
        $site = SiteDefinition::first();

        $plSite = new SiteDefinition();
        $plSite->is_prefixed = true;
        $plSite->name = 'Polish';
        $plSite->code = 'pl';
        $plSite->route_prefix = '/pl';
        $plSite->locale = 'pl';
        $plSite->save();

        $settings = Settings::instance();
        $settings->enable_sitemap = true;
        $settings->sitemap_enabled_for_sites = ['primary', 'pl'];
        $settings->save();

        $theme = Theme::load('test');
        StaticPage::clearCache($theme);

        $pagesGenerator = new PagesGenerator($site);
        $staticPage = $pagesGenerator->getEnabledStaticPages($theme)[0];
        $pagesGenerator->refreshForStaticPage($staticPage);

        $this->assertEquals(1, SitemapItem::count());

        $pagesGenerator = new PagesGenerator($plSite);
        $staticPage = $pagesGenerator->getEnabledStaticPages($theme)[0];
        $pagesGenerator->refreshForStaticPage($staticPage);

        $this->assertEquals(2, SitemapItem::count());

        $enSitemapItem = SitemapItem::where('site_definition_id', $site->id)->first();
        $url = str_replace(url('/'), 'http://initwebsite.devt', $enSitemapItem->loc);
        $this->assertEquals('http://initwebsite.devt/test-static', $url);

        $plSitemapItem = SitemapItem::where('site_definition_id', $plSite->id)->first();
        $url = str_replace(url('/'), 'http://initwebsite.devt', $plSitemapItem->loc);
        $this->assertEquals('http://initwebsite.devt/pl/test-statyczna', $url);

        // TODO: Watch out - it will remove the file for real
        // $staticPage->delete();

        // $this->assertEquals(0, SitemapItem::count());
    }
}
