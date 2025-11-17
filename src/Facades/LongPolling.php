<?php

/**
 * LongPolling.php
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void broadcast(string $channelId, array $payload, string $type = 'event')
 * @method static void broadcastNow(string $channelId, array $payload, string $type = 'event')
 * @method static string getToken(string $channelId)
 * @method static int getLastOffset(string $channelId, array $types = [])
 * @method static array getLastEvents(string $channelId, int $count = 10, array $types = [])
 * @method static array getUpdates(string $channelId, int $fromOffset, int $limit = 100)
 * @method static int clear(string|null $channelId = null, array $types = [], int|null $ttl = null)
 *
 * @see \Levskiy0\LongPolling\Contracts\LongPollingContract
 */
class LongPolling extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'long-polling';
    }
}
