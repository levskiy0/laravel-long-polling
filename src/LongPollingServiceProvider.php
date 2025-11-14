<?php
/**
 * LongPollingServiceProvider.php
 * Service provider for Laravel Long-Polling system
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Levskiy0\LongPolling\Contracts\LongPollingContract;
use Levskiy0\LongPolling\Drivers\LongPollingServer;
use Levskiy0\LongPolling\Http\Controllers\EventsController;

class LongPollingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/long-polling.php', 'long-polling');

        $this->app->singleton(LongPollingContract::class, function ($app) {
            $driver = config('long-polling.driver');

            return match ($driver) {
                'redis' => new LongPollingServer(
                    connection: config('long-polling.redis.connection'),
                    channel: config('long-polling.redis.channel'),
                ),
                default => throw new \InvalidArgumentException("Unsupported driver: {$driver}"),
            };
        });

        $this->app->alias(LongPollingContract::class, 'long-polling');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/long-polling.php' => config_path('long-polling.php'),
            ], 'long-polling-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'long-polling-migrations');
        }
    }

    /**
     * Register package routes
     */
    private function registerRoutes(): void
    {
        Route::prefix('api/long-polling')
            ->group(function () {
                Route::get('/getEvents', [EventsController::class, 'getEvents'])
                    ->name('long-polling.getEvents');
            });
    }
}
