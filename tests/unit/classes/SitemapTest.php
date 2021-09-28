<?php

namespace Initbiz\SeoStorm\Tests\Unit\Components;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Initbiz\SeoStorm\Classes\Sitemap;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;

class SitemapTest extends StormedTestCase
{
    public function testGenerate()
    {
        $pages = Page::listInTheme(Theme::getEditTheme());
        $sitemap = new Sitemap();
        $xml = $sitemap->generate();

        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/reference/sitemap-2-pages.xml');
        $this->assertXmlStringEqualsXmlFile($filePath, $xml);
    }
}
