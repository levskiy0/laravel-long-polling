<?php

/**
 * BroadcastEventJob.php
 * Job for broadcasting long-polling events through queue
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $channelId,
        private readonly array $payload,
        private readonly bool $saveEvent = true,
    ) {}

    public function handle(): void
    {
        // Get the underlying driver directly, not the dispatcher
        $driver = app('long-polling.driver');
        $driver->broadcast($this->channelId, $this->payload, $this->saveEvent);
    }
}
