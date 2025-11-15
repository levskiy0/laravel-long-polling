<?php

/**
 * LongPollingServer.php
 * Long-polling server driver for broadcasting events
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Redis;
use Levskiy0\LongPolling\Contracts\LongPollingContract;
use Levskiy0\LongPolling\Models\LongPollingEvent;

class LongPollingServer implements LongPollingContract
{
    private Client $httpClient;

    public function __construct(
        private readonly string $connection,
        private readonly string $channel,
    ) {
        $this->httpClient = new Client([
            'timeout' => 5,
            'verify' => false,
        ]);
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
     * Get JWT access token from Go service
     */
    public function getToken(string $channelId): string
    {
        $goServiceUrl = config('long-polling.go_service_url');
        $accessSecret = config('long-polling.access_secret');

        try {
            $response = $this->httpClient->post("{$goServiceUrl}/getAccessToken", [
                'query' => [
                    'channel_id' => $channelId,
                    'secret' => $accessSecret,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['token'] ?? throw new \RuntimeException('Token not found in response');
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Failed to obtain token from Go service: {$e->getMessage()}", 0, $e);
        }
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

    public function getLastOffset(string $channelId): int
    {
        return LongPollingEvent::getLastOffset($channelId);
    }

    public function getLastEvents(string $channelId, int $count = 10): array
    {
        return LongPollingEvent::getLastEvents($channelId, $count);
    }

    public function getUpdates(string $channelId, int $fromOffset, int $limit = 100): array
    {
        return LongPollingEvent::getUpdates($channelId, $fromOffset, $limit);
    }
}
