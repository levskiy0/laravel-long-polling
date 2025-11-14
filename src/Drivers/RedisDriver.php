<?php
/**
 * RedisDriver.php
 * Redis driver for broadcasting long-polling events
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Drivers;

use Illuminate\Support\Facades\Redis;
use Levskiy0\LongPolling\Contracts\LongPollingDriver;
use Levskiy0\LongPolling\Models\LongPollingEvent;

class RedisDriver implements LongPollingDriver
{
    public function __construct(
        private readonly string $connection,
        private readonly string $channel,
    ) {
    }

    /**
     * Broadcast an event to a specific channel
     *
     * This method:
     * 1. Stores the event in the database
     * 2. Publishes a notification to Redis for real-time delivery
     */
    public function broadcast(string $channelId, array $payload): void
    {
        $event = LongPollingEvent::storeEvent($channelId, $payload);
        $this->publishToRedis($channelId, $event->id);
    }

    /**
     * Publish a notification to Redis
     */
    private function publishToRedis(string $channelId, int $eventId): void
    {
        $message = json_encode([
            'channel_id' => $channelId,
            'event_id' => $eventId,
            'timestamp' => now()->timestamp,
        ]);

        Redis::connection($this->connection)
            ->publish($this->channel, $message);
    }
}
