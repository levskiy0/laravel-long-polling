<?php
/**
 * LongPolling.php
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Facades;

use Illuminate\Support\Facades\Facade;

class LongPolling extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'long-polling';
    }
}