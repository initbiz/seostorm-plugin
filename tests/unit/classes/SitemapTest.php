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
        $pages = Page::listInTheme(Theme::getEditTheme());

        $sitemap = new Sitemap();
        $xml = $sitemap->generate($pages);

        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-1-page.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);

        foreach ($pages as $page) {
            if ($page->url === '/') {
                $page->settings['seo_options_enabled_in_sitemap'] = "true";
            }
        }

        $sitemap = new Sitemap();
        $xml = $sitemap->generate($pages);
        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-2-pages.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }
}
