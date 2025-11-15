<?php

/**
 * EventsControllerTest.php
 * Tests for EventsController validation and security
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 15.11.2025
 */

namespace Levskiy0\LongPolling\Tests\Feature;

use Levskiy0\LongPolling\Tests\TestCase;

class EventsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['long-polling.access_secret' => 'test-secret']);
    }

    public function test_rejects_negative_limit(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?'.http_build_query([
            'channel_id' => 'test-channel',
            'secret' => 'test-secret',
            'limit' => -5,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['limit']);
    }

    public function test_rejects_zero_limit(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?'.http_build_query([
            'channel_id' => 'test-channel',
            'secret' => 'test-secret',
            'limit' => 0,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['limit']);
    }

    public function test_rejects_limit_above_100(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?'.http_build_query([
            'channel_id' => 'test-channel',
            'secret' => 'test-secret',
            'limit' => 150,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['limit']);
    }

    public function test_rejects_negative_offset(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?'.http_build_query([
            'channel_id' => 'test-channel',
            'secret' => 'test-secret',
            'offset' => -10,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['offset']);
    }

    public function test_accepts_valid_parameters(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?'.http_build_query([
            'channel_id' => 'test-channel',
            'secret' => 'test-secret',
            'offset' => 5,
            'limit' => 50,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'events',
            'count',
        ]);
    }

    public function test_uses_default_values_when_omitted(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?'.http_build_query([
            'channel_id' => 'test-channel',
            'secret' => 'test-secret',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'events',
            'count',
        ]);
    }

    public function test_rejects_unauthorized_requests(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?'.http_build_query([
            'channel_id' => 'test-channel',
            'secret' => 'wrong-secret',
        ]));

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    public function test_rejects_missing_channel_id(): void
    {
        $response = $this->getJson('/api/long-polling/getEvents?'.http_build_query([
            'secret' => 'test-secret',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['channel_id']);
    }
}
