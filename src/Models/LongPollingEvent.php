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
        'type',
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
    public static function storeEvent(string $channelId, array $payload, string $type = 'event'): self
    {
        return static::create([
            'channel_id' => $channelId,
            'type' => $type,
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

    /**
     * Get the last offset (event ID) for a specific channel and type
     */
    public static function getLastOffsetByType(string $channelId, string $type): int
    {
        return static::query()
            ->where('channel_id', $channelId)
            ->where('type', $type)
            ->max('id') ?? 0;
    }

    /**
     * Clear events by type with optional TTL filter
     */
    public static function clearByType(string $channelId, string $type, ?int $ttl = null): int
    {
        $query = static::query()
            ->where('channel_id', $channelId)
            ->where('type', $type);

        if ($ttl !== null) {
            $query->where('created_at', '<', now()->subSeconds($ttl));
        }

        return $query->delete();
    }

    /**
     * Clear events with optional channel and TTL filters
     */
    public static function clear(?string $channelId = null, ?int $ttl = null): int
    {
        $query = static::query();

        if ($channelId !== null) {
            $query->where('channel_id', $channelId);
        }

        if ($ttl !== null) {
            $query->where('created_at', '<', now()->subSeconds($ttl));
        }

        return $query->delete();
    }
}
