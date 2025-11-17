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
     *
     * @param string $channelId Channel identifier
     * @param array $payload Event data
     * @param bool $saveEvent Whether to save event to database (default: true)
     */
    public function broadcast(string $channelId, array $payload, bool $saveEvent = true): void;

    /**
     * Broadcast an event to a channel immediately (synchronous, bypassing queue)
     *
     * @param string $channelId Channel identifier
     * @param array $payload Event data
     * @param bool $saveEvent Whether to save event to database (default: true)
     */
    public function broadcastNow(string $channelId, array $payload, bool $saveEvent = true): void;

    public function getToken(string $channelId): string;

    public function getLastOffset(string $channelId): int;

    public function getLastEvents(string $channelId, int $count = 10): array;

    public function getUpdates(string $channelId, int $fromOffset, int $limit = 100): array;
}
