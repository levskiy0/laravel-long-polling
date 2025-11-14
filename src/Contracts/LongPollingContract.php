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
}