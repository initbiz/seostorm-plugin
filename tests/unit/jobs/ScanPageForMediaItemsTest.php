<?php

namespace Initbiz\SeoStorm\Tests\Unit\Jobs;

use Queue;
use PluginTestCase;
use Initbiz\SeoStorm\Jobs\ScanPageForMediaItems;

class ScanPageForMediaItemsTest extends PluginTestCase
{
    public function testMarkingAsPending(): void
    {
        Queue::fake();

        $loc = 'http://example.com';
        $loc2 = 'http://example.com/2';

        $this->assertFalse(ScanPageForMediaItems::isPending($loc));

        ScanPageForMediaItems::markAsPending($loc2);
        $this->assertFalse(ScanPageForMediaItems::isPending($loc));

        ScanPageForMediaItems::markAsPending($loc);
        $this->assertTrue(ScanPageForMediaItems::isPending($loc));

        ScanPageForMediaItems::unmarkAsPending($loc);
        $this->assertFalse(ScanPageForMediaItems::isPending($loc));
    }
}
