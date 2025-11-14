<?php
/**
 * EventsEndpointTest.php
 * Tests for /getEvents endpoint
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Tests\Feature;

use Levskiy0\LongPolling\Models\LongPollingEvent;
use Levskiy0\LongPolling\Tests\TestCase;

class EventsEndpointTest extends TestCase
{
    public function test_get_events_requires_authentication(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?channel_id=test');

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_get_events_requires_channel_id(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?secret=test_secret');

        $response->assertStatus(400)
            ->assertJson(['error' => 'channel_id is required']);
    }

    public function test_get_events_returns_events(): void
    {
        LongPollingEvent::storeEvent('test-channel', ['type' => 'test', 'data' => 'event1']);
        LongPollingEvent::storeEvent('test-channel', ['type' => 'test', 'data' => 'event2']);
        LongPollingEvent::storeEvent('other-channel', ['type' => 'test', 'data' => 'event3']);

        $response = $this->getJson('/api/long-polling/getEvents?secret=test_secret&channel_id=test-channel');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'events' => [
                    '*' => ['id', 'event', 'created_at'],
                ],
                'count',
            ])
            ->assertJsonCount(2, 'events');
    }

    public function test_get_events_respects_offset(): void
    {
        $event1 = LongPollingEvent::storeEvent('test-channel', ['type' => 'test', 'data' => 'event1']);
        $event2 = LongPollingEvent::storeEvent('test-channel', ['type' => 'test', 'data' => 'event2']);

        $response = $this->getJson('/api/long-polling/getEvents?secret=test_secret&channel_id=test-channel&offset='.$event1->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'events');
    }

    public function test_get_events_respects_limit(): void
    {
        for ($i = 0; $i < 10; $i++) {
            LongPollingEvent::storeEvent('test-channel', ['type' => 'test', 'data' => "event{$i}"]);
        }

        $response = $this->getJson('/api/long-polling/getEvents?secret=test_secret&channel_id=test-channel&limit=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'events');
    }
}
