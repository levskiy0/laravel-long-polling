<?php
/**
 * LongPollManager.php
 * Main service for managing long-polling broadcasts
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling;

use Levskiy0\LongPolling\Jobs\BroadcastEventJob;

class LongPollingManager
{
    public function __construct(
        private readonly string $broadcastQueue,
    ) {
    }

    /**
     * Broadcast an event to a specific channel
     *
     * This method dispatches a job to the broadcast queue
     * which will handle storing the event and notifying subscribers
     *
     * @param int|string $channelId The channel ID to broadcast to
     * @param array $payload The event payload
     */
    public function broadcast(int|string $channelId, array $payload): void
    {
        BroadcastEventJob::dispatch(
            (string) $channelId,
            $payload
        )->onQueue($this->broadcastQueue);
    }

    /**
     * Get the Go service URL for client connections
     */
    public function getClientUrl(): string
    {
        return config('long-polling.go_service_url');
    }

    /**
     * Get the access secret for authentication
     */
    public function getAccessSecret(): string
    {
        return config('long-polling.access_secret');
    }
}
