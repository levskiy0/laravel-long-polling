<?php

/**
 * LongPollingDispatcher.php
 * Dispatcher that queues broadcast events instead of executing them synchronously
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 15.11.2025
 */

namespace Levskiy0\LongPolling;

use Levskiy0\LongPolling\Contracts\LongPollingContract;
use Levskiy0\LongPolling\Jobs\BroadcastEventJob;

class LongPollingDispatcher implements LongPollingContract
{
    public function __construct(
        private readonly LongPollingContract $driver,
        private readonly string $queue,
    ) {}

    public function broadcast(string $channelId, array $payload, string $type = 'event'): void
    {
        BroadcastEventJob::dispatch($channelId, $payload, $type)
            ->onQueue($this->queue);
    }

    public function broadcastNow(string $channelId, array $payload, string $type = 'event'): void
    {
        // Bypass queue and call driver directly
        $this->driver->broadcastNow($channelId, $payload, $type);
    }

    public function getToken(string $channelId): string
    {
        return $this->driver->getToken($channelId);
    }

    public function getLastOffset(string $channelId, array $types = []): int
    {
        return $this->driver->getLastOffset($channelId, $types);
    }

    public function getLastEvents(string $channelId, int $count = 10, array $types = []): array
    {
        return $this->driver->getLastEvents($channelId, $count, $types);
    }

    public function getUpdates(string $channelId, int $fromOffset, int $limit = 100): array
    {
        return $this->driver->getUpdates($channelId, $fromOffset, $limit);
    }

    public function clear(?string $channelId = null, array $types = [], ?int $ttl = null): int
    {
        return $this->driver->clear($channelId, $types, $ttl);
    }
}
