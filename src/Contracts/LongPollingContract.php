<?php

/**
 * LongPollingContract.php
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Contracts;

interface LongPollingContract
{
    /**
     * Broadcast an event to a channel (queued)
     */
    public function broadcast(string $channelId, array $payload, string $type = 'event'): void;

    /**
     * Broadcast an event to a channel immediately (synchronous, bypassing queue)
     */
    public function broadcastNow(string $channelId, array $payload, string $type = 'event'): void;

    public function getToken(string $channelId): string;

    public function getLastOffset(string $channelId): int;

    public function getLastOffsetByType(string $channelId, string $type): int;

    public function getLastEvents(string $channelId, int $count = 10): array;

    public function getUpdates(string $channelId, int $fromOffset, int $limit = 100): array;

    public function clearByType(string $channelId, string $type, ?int $ttl = null): int;

    public function clear(?string $channelId = null, ?int $ttl = null): int;
}
