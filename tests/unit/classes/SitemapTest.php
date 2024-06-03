<?php

namespace Initbiz\SeoStorm\Tests\Unit\Classes;

use Config;
use Carbon\Carbon;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Initbiz\SeoStorm\Classes\Sitemap;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedCategory;

class SitemapTest extends StormedTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $themesPath = 'plugins/initbiz/seostorm/tests/themes';
        Config::set('system.themes_path', $themesPath);
        app()->useThemesPath($themesPath);
    }

    public function testEnabledInSitemap()
    {
        $theme = Theme::load('test');
        $page1 = Page::load($theme, 'empty');
        $page1->mtime = 1632857872;
        $page2 = Page::load($theme, 'with-fake-model');
        $page2->mtime = 1632858273;
        $pages = collect([$page1, $page2]);

        $xml = (new Sitemap())->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-1-page.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);

        $page1->settings['seoOptionsEnabledInSitemap'] = "true";
        $pages = collect([$page1, $page2]);

        $xml = (new Sitemap)->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-2-pages.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testHasModelClass()
    {
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

        $xml = (new Sitemap)->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-slugs.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);

        // Test if sitemap has filtered record using the active scope

        $model2->is_active = false;
        $model2->save();

        $page->settings['seoOptionsModelScope'] = "active";
        $pages = collect();
        $pages = $pages->push($page);

        $xml = (new Sitemap)->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-slugs-filtered.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testParamsWithRelation()
    {
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

        $xml = (new Sitemap)->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-slugs-relation.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testUseUpdatedAt()
    {
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

        $xml = (new Sitemap)->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-updated-at.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testDisabledInModel()
    {
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

        $xml = (new Sitemap)->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-slugs-filtered.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testOptionalParameterEmpty()
    {
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
        $xml = (new Sitemap)->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-empty-optional-param.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);

        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model-optional');
        $page->mtime = 1632858273;
        $pages = collect();
        $pages = $pages->push($page);
        $xml = (new Sitemap)->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-empty-optional-param-2.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testOptionalScopeParameter()
    {
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
        $xml = (new Sitemap)->generate($pages);
        $xml = str_replace(url('/'), 'http://initwebsite.devt', $xml);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-empty-optional-param.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }
}
