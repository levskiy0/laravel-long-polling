<?php

/**
 * LongPollingContract.php
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Contracts;

use Levskiy0\LongPolling\Models\LongPollingEvent;

interface LongPollingContract
{
    /**
     * Broadcast an event to a channel (queued)
     */
    public function broadcast(string $channelId, array $payload, string $type = 'event'): void;

    /**
     * Broadcast an event to a channel immediately (synchronous, bypassing queue)
     */
    public function broadcastNow(string $channelId, array $payload, string $type = 'event'): LongPollingEvent;

    public function getToken(string $channelId): string;

    public function getLastOffset(string $channelId, array $types = []): int;

    public function getLastEvents(string $channelId, int $count = 10, array $types = []): array;

    public function getUpdates(string $channelId, int $fromOffset, int $limit = 100): array;

    public function clear(?string $channelId = null, array $types = [], ?int $ttl = null): int;
}
