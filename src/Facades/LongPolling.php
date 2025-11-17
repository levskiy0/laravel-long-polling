<?php

/**
 * LongPolling.php
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void broadcast(string $channelId, array $payload, bool $saveEvent = true)
 * @method static void broadcastNow(string $channelId, array $payload, bool $saveEvent = true)
 * @method static string getToken(string $channelId)
 * @method static int getLastOffset(string $channelId)
 * @method static array getLastEvents(string $channelId, int $count = 10)
 * @method static array getUpdates(string $channelId, int $fromOffset, int $limit = 100)
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
