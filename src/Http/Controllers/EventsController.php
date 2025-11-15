<?php

/**
 * EventsController.php
 * Controller for handling event retrieval from Go service
 *
 * Author: 40x.Pro@gmail.com | github.com/levskiy0
 * Date: 14.11.2025
 */

namespace Levskiy0\LongPolling\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Levskiy0\LongPolling\Models\LongPollingEvent;

class EventsController extends Controller
{
    /**
     * Get events for a specific channel
     *
     * This endpoint is called by the Go service to fetch events from the database
     * Authentication is done via the shared secret
     *
     * Query parameters:
     * - channel_id: The channel to fetch events for
     * - secret: Shared secret for authentication
     * - offset: Last event ID received (default: 0)
     * - limit: Maximum number of events to return (default: 100, max: 100)
     */
    public function getEvents(Request $request): JsonResponse
    {
        $providedSecret = $request->query('secret');
        $expectedSecret = config('long-polling.access_secret');

        if ($providedSecret !== $expectedSecret) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        $channelId = $request->query('channel_id');
        if (! $channelId) {
            return response()->json([
                'error' => 'channel_id is required',
            ], 400);
        }

        $offset = (int) $request->query('offset', 0);
        $limit = min((int) $request->query('limit', 100), 100);
        $events = LongPollingEvent::getEvents($channelId, $offset, $limit);

        return response()->json([
            'events' => $events,
            'count' => count($events),
        ]);
    }
}
