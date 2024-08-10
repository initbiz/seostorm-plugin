<?php

namespace Initbiz\SeoStorm\Tests\Unit\Jobs;

use Queue;
use PluginTestCase;
use Initbiz\SeoStorm\Jobs\ScanPageForMediaItems;
use Initbiz\SeoStorm\Jobs\UniqueQueueJobDispatcher;

class UniqueQueueJobDispatcherTest extends PluginTestCase
{
    public function testMarkingAsPending(): void
    {
        $jobClass = ScanPageForMediaItems::class;

        $data1 = [
            'loc' => 'http://example.com'
        ];

        $data2 = [
            'loc' => 'http://example.com/2'
        ];

        $jobDispatcher = UniqueQueueJobDispatcher::instance();

        $this->assertFalse($jobDispatcher->isPending($jobClass, $data1));

        // marking data2 as pending, checking if data1 still not pending
        $jobDispatcher->markAsPending($jobClass, $data2);
        $this->assertFalse($jobDispatcher->isPending($jobClass, $data1));

        $jobDispatcher->markAsPending($jobClass, $data1);
        $this->assertTrue($jobDispatcher->isPending($jobClass, $data1));

        $jobDispatcher->unmarkAsPending($jobClass, $data1);
        $this->assertFalse($jobDispatcher->isPending($jobClass, $data1));
    }

    public function testPushingJob(): void
    {
        Queue::fake();

        $jobDispatcher = UniqueQueueJobDispatcher::instance();
        $jobDispatcher->resetCache();

        $jobClass = ScanPageForMediaItems::class;

        $data1 = [
            'loc' => 'http://example.com'
        ];

        $data2 = [
            'loc' => 'http://example.com/2'
        ];

        $jobDispatcher->push($jobClass, $data1);

        Queue::assertPushed($jobClass, 1);

        $jobDispatcher->push($jobClass, $data2);

        Queue::assertPushed($jobClass, 2);

        // Try to push the queue job once again
        $jobDispatcher->push($jobClass, $data2);
        $jobDispatcher->push($jobClass, $data1);

        Queue::assertPushed($jobClass, 2);
    }
}
