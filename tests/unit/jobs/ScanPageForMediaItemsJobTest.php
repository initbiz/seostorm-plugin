<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Tests\Unit\Jobs;

use Request;
use Initbiz\SeoStorm\Models\SitemapItem;
use Initbiz\SeoStorm\Jobs\ScanPageForMediaItemsJob;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;

class ScanPageForMediaItemsJobTest extends StormedTestCase
{
    public function testRestoresRequestAfterNotFoundPage(): void
    {
        $sitemapItem = new SitemapItem();
        $sitemapItem->loc = 'https://example.com/not-existing-page';
        $sitemapItem->base_file_name = 'not-existing-page';
        $sitemapItem->save();

        $originalRequest = Request::getFacadeRoot();

        (new ScanPageForMediaItemsJob())->scan($sitemapItem->loc);

        $this->assertSame($originalRequest, Request::getFacadeRoot());
    }
}
