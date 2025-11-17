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
     *
     * @param string $channelId Channel identifier
     * @param array $types Event types to filter (empty array = all types)
     */
    public static function getLastOffset(string $channelId, array $types = []): int
    {
        $query = static::query()
            ->where('channel_id', $channelId);

        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        return $query->max('id') ?? 0;
    }

    /**
     * Get the last N events from the channel
     *
     * @param string $channelId Channel identifier
     * @param int $count Number of events to retrieve
     * @param array $types Event types to filter (empty array = all types)
     */
    public static function getLastEvents(string $channelId, int $count = 10, array $types = []): array
    {
        $query = static::query()
            ->where('channel_id', $channelId);

        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        return $query
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
     * Clear events with optional channel, types, and TTL filters
     *
     * @param string|null $channelId Channel identifier (null = all channels)
     * @param array $types Event types to filter (empty array = all types)
     * @param int|null $ttl Time to live in seconds (only delete events older than this)
     */
    public static function clear(?string $channelId = null, array $types = [], ?int $ttl = null): int
    {
        $query = static::query();

        if ($channelId !== null) {
            $query->where('channel_id', $channelId);
        }

        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        if ($ttl !== null) {
            $query->where('created_at', '<', now()->subSeconds($ttl));
        }

        return $query->delete();
    }
}
