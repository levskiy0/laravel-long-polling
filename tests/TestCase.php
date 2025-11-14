<?php
/**
 * TestCase.php
 * Base test case for long-polling package tests
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Levskiy0\LongPolling\LongPollingServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            LongPollingServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'LongPolly' => \Levskiy0\LongPolling\Facades\LongPolling::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('long-polling.access_secret', 'test_secret');
        $app['config']->set('long-polling.broadcast_queue', 'broadcast');
        $app['config']->set('long-polling.redis.connection', 'default');
        $app['config']->set('long-polling.redis.channel', 'longpoll:events');
    }
}
