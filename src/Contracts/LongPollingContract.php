<?php

/**
 * LongPollingContract.php
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Contracts;

interface LongPollingContract
{
    public function broadcast(string $channelId, array $payload): void;

    public function getToken(string $channelId): string;

    public function getLastOffset(string $channelId): int;

    public function getLastEvents(string $channelId, int $count = 10): array;

    public function getUpdates(string $channelId, int $fromOffset, int $limit = 100): array;
}
