<?php

/**
 * LongPollEvent.php
 * Model for long polling events
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Models;

use Illuminate\Database\Eloquent\Model;

class LongPollingEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'channel_id',
        'event',
    ];

    protected $casts = [
        'event' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get events for a specific channel starting from offset
     */
    public static function getEvents(string $channelId, int $offset = 0, int $limit = 100): array
    {
        return static::query()
            ->where('channel_id', $channelId)
            ->where('id', '>', $offset)
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'event' => $event->event,
                'created_at' => $event->created_at->timestamp,
            ])
            ->toArray();
    }

    /**
     * Store a new event
     */
    public static function storeEvent(string $channelId, array $payload): self
    {
        return static::create([
            'channel_id' => $channelId,
            'event' => $payload,
        ]);
    }

    /**
     * Get the last offset (event ID) for a specific channel
     */
    public static function getLastOffset(string $channelId): int
    {
        return static::query()
            ->where('channel_id', $channelId)
            ->max('id') ?? 0;
    }

    /**
     * Get the last N events from the channel
     */
    public static function getLastEvents(string $channelId, int $count = 10): array
    {
        return static::query()
            ->where('channel_id', $channelId)
            ->orderBy('id', 'desc')
            ->limit($count)
            ->get()
            ->reverse()
            ->map(fn ($event) => [
                'id' => $event->id,
                'event' => $event->event,
                'created_at' => $event->created_at->timestamp,
            ])
            ->toArray();
    }

    /**
     * Get updates (events) from offset
     */
    public static function getUpdates(string $channelId, int $fromOffset, int $limit = 100): array
    {
        return static::query()
            ->where('channel_id', $channelId)
            ->where('id', '>', $fromOffset)
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'event' => $event->event,
                'created_at' => $event->created_at->timestamp,
            ])
            ->toArray();
    }
}
