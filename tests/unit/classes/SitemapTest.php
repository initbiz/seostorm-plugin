<?php

namespace Initbiz\SeoStorm\Tests\Unit\Components;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Initbiz\SeoStorm\Classes\Sitemap;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;

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
}
