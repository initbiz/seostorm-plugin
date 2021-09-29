<?php

namespace Initbiz\SeoStorm\Tests\Unit\Components;

use Cms\Classes\Page;
use Initbiz\SeoStorm\Classes\Sitemap;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;

class SitemapTest extends StormedTestCase
{
    public function testEnabledInSitemap()
    {
        $page1 = Page::where('url', '/')->first();
        $page1->mtime = 1632857872;
        $page2 = Page::where('url', '/model/:slug')->first();
        $page2->mtime = 1632858273;
        $pages = collect([$page1, $page2]);

        $xml = (new Sitemap())->generate($pages);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-1-page.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);

        $page1->settings['seo_options_enabled_in_sitemap'] = "true";
        $pages = collect([$page1, $page2]);

        $xml = (new Sitemap)->generate($pages);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-2-pages.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }

    public function testHasModelClass()
    {
        $page = Page::where('url', '/model/:slug')->first();
        $page->mtime = 1632858273;
        $page->settings['seo_options_enabled_in_sitemap'] = "true";
        $page->settings['seo_options_model_class'] = "\Initbiz\SeoStorm\Tests\Classes\FakeStormedModel";
        $page->settings['seo_options_model_params'] = "slug:slug";
        $pages = collect();
        $pages = $pages->push($page);

        $model = new FakeStormedModel();
        $model->name = 'test-name';
        $model->slug = 'test-slug';
        $model->save();

        $model2 = new FakeStormedModel();
        $model2->name = 'test-name-2';
        $model2->slug = 'test-slug-2';
        $model2->save();

        $xml = (new Sitemap)->generate($pages);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-slugs.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }
}
