<?php
/**
 * LongPollDriver.php
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Contracts;

interface LongPollingDriver
{
    public function broadcast(string $channelId, array $payload): void;
}