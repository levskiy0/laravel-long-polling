<?php
/**
 * long-polling.php
 * Configuration for Laravel Long-Polling system
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

return [
    // Driver for broadcasting events (currently only 'redis' is supported)
    'driver' => env('LONGPOLLING_DRIVER', 'redis'),

    // Go service URL for client connections
    'go_service_url' => env('LONGPOLLING_GO_SERVICE_URL', 'http://localhost:8085'),

    // Shared secret for authentication between Laravel and Go service
    'access_secret' => env('LONGPOLLING_ACCESS_SECRET', 'shared_secret_between_laravel_and_go'),

    // Queue name for broadcasting events
    'broadcast_queue' => env('LONGPOLLING_BROADCAST_QUEUE', 'broadcast'),

    // Redis configuration
    'redis' => [
        'connection' => env('LONGPOLLING_REDIS_CONNECTION', 'default'),
        'channel'    => env('LONGPOLLING_REDIS_CHANNEL', 'longpoll:events'),
    ],
];