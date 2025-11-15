<?php
/**
 * BroadcastTest.php
 * Tests for broadcast functionality
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Levskiy0\LongPolling\Facades\LongPolling;
use Levskiy0\LongPolling\Jobs\BroadcastEventJob;
use Levskiy0\LongPolling\Tests\TestCase;

class BroadcastTest extends TestCase
{
    public function test_broadcast_dispatches_job(): void
    {
        Queue::fake();

        LongPolling::broadcast(123, ['type' => 'test', 'message' => 'Hello']);

        Queue::assertPushed(BroadcastEventJob::class, function ($job) {
            return $job->queue === 'broadcast';
        });
    }

    public function test_broadcast_job_stores_event(): void
    {
        $channelId = 'test-channel';
        $payload = ['type' => 'test', 'message' => 'Hello World'];

        $job = new BroadcastEventJob($channelId, $payload);
        $job->handle();

        $this->assertDatabaseHas('long_polling_events', [
            'channel_id' => $channelId,
        ]);
    }
}
