# Laravel Long-Polling Package

Laravel package for long-polling functionality with Go service integration.

👇👇👇

[[Go Long-Polling Service]](https://github.com/levskiy0/go-laravel-long-polling)

## Features

- **Queue-based Broadcasting**: Events are queued for reliable delivery
- **Redis Integration**: Real-time notifications via Redis pub/sub
- **Database Persistence**: All events stored in database for reliability
- **Simple API**: Easy-to-use facade for broadcasting events
- **RESTful Endpoint**: Secure endpoint for Go service to fetch events
- **Comprehensive Tests**: Full test coverage with PHPUnit/Pest

## Installation

### 1. Install the Package

If using as a local package (monorepo):

```bash
composer require levskiy0/long-polling
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=long-polling-config
```

### 3. Configure Environment

Add to your `.env`:

```env
LONGPOLLING_DRIVER=redis
LONGPOLLING_GO_SERVICE_URL=http://localhost:8085
LONGPOLLING_ACCESS_SECRET=shared_secret_between_laravel_and_go
LONGPOLLING_BROADCAST_QUEUE=broadcast
LONGPOLLING_REDIS_CONNECTION=default
LONGPOLLING_REDIS_CHANNEL=longpoll:events
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Configure Queue Worker

Make sure you have a queue worker running for the broadcast queue:

```bash
php artisan queue:work --queue=broadcast
```

## Configuration

The configuration file is located at `config/long-polling.php`:

```php
return [
    'driver' => env('LONGPOLLING_DRIVER', 'redis'),
    'go_service_url' => env('LONGPOLLING_GO_SERVICE_URL', 'http://localhost:8085'),
    'access_secret' => env('LONGPOLLING_ACCESS_SECRET', 'shared_secret_between_laravel_and_go'),
    'broadcast_queue' => env('LONGPOLLING_BROADCAST_QUEUE', 'broadcast'),
    'redis' => [
        'connection' => env('LONGPOLLING_REDIS_CONNECTION', 'default'),
        'channel' => env('LONGPOLLING_REDIS_CHANNEL', 'longpoll:events'),
    ],
];
```

## Usage

### Broadcasting Events

```php
use Levskiy0\LongPolling\Facades\LongPolling;

// Broadcast an event to a channel
LongPolling::broadcast(
    channelId: 'user-123',
    payload: [
        'type' => 'notification',
        'message' => 'You have a new message',
        'data' => [
            'sender' => 'John Doe',
            'timestamp' => now()->toISOString(),
        ]
    ]
);
```

### Direct Usage (Without Queue)

If you need to broadcast synchronously:

```php
use Levskiy0\LongPolling\Contracts\LongPollingContract;

$driver = app(LongPollingContract::class);
$driver->broadcast('channel-id', ['type' => 'event', 'data' => '...']);
```

### Client Integration

From your frontend, connect to the Go service:

```javascript
// 1. Get access token from your Laravel backend
const response = await fetch('/api/longpolling/token', {
  method: 'POST',
  body: JSON.stringify({ channel_id: 'user-123' })
});
const { token } = await response.json();

// 2. Start long-polling
async function poll(offset = 0) {
  const response = await fetch(
    `http://localhost:8085/getUpdates?token=${token}&offset=${offset}`
  );
  const { events } = await response.json();

  // Process events
  events.forEach(event => {
    console.log('New event:', event);
  });

  // Continue polling with latest offset
  const lastOffset = events.length > 0
    ? events[events.length - 1].id
    : offset;

  poll(lastOffset);
}

poll();
```

## Example: Building a Real-Time Chat

This example demonstrates how to build a complete real-time chat application using the Long-Polling package.

### Step 1: Create Chat Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Levskiy0\LongPolling\Facades\LongPolling;

class ChatController extends Controller
{
    private const CHANNEL_ID = 'chat';

    public function index(): View
    {
        return view('chat.index');
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Broadcast message through long polling
        LongPolling::broadcast(self::CHANNEL_ID, [
            'type' => 'message',
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'message' => $request->message,
            'timestamp' => now()->toISOString(),
        ]);

        return response()->json(['success' => true]);
    }

    public function getState(): JsonResponse
    {
        $driver = app(\Levskiy0\LongPolling\Contracts\LongPollingContract::class);
        $lastOffset = LongPolling::getLastOffset(self::CHANNEL_ID);
        $offsetWithMargin = max(0, $lastOffset - 10);

        $goServiceUrl = config('long-polling.go_service_url');
        $getUpdatesUrl = "{$goServiceUrl}/getUpdates?channel_id=".self::CHANNEL_ID;

        return response()->json([
            'offset' => $offsetWithMargin,
            'token' => $driver->getToken(self::CHANNEL_ID),
            'get_updates_url' => $getUpdatesUrl,
        ]);
    }
}
```

### Step 2: Register Routes

```php
// routes/web.php

Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/state', [ChatController::class, 'getState'])->name('chat.state');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
});
```

### Step 3: Create Frontend

```html
<script>
    let currentOffset = 0;
    let pollingActive = false;
    let getUpdatesUrl = '';
    let token = '';

    // Initialize chat
    async function initialize() {
        // Get initial state (offset with -10 margin, token, updates URL)
        const response = await fetch('/chat/state');
        const data = await response.json();

        currentOffset = data.offset;
        getUpdatesUrl = data.get_updates_url;
        token = data.token;

        // Start long polling from offset with -10 margin
        // This will load the last ~10 messages on first poll
        startLongPolling();
    }

    // Start long polling from GO service
    function startLongPolling() {
        if (pollingActive) return;
        pollingActive = true;
        pollForMessages();
    }

    async function pollForMessages() {
        while (pollingActive) {
            try {
                // Call GO service directly
                const url = `${getUpdatesUrl}&offset=${currentOffset}&token=${encodeURIComponent(token)}`;
                const response = await fetch(url);
                const data = await response.json();

                if (data.events && data.events.length > 0) {
                    data.events.forEach(event => displayMessage(event));
                    currentOffset = data.events[data.events.length - 1].id;
                }
            } catch (error) {
                console.error('Polling error:', error);
                await new Promise(resolve => setTimeout(resolve, 3000));
            }
        }
    }

    // Send message
    async function sendMessage(message) {
        await fetch('/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message })
        });
    }

    function displayMessage(event) {
        const msg = event.event;
        console.log(`${msg.user_name}: ${msg.message}`);
    }

    // Initialize on page load
    initialize();
</script>
```

### Step 4: Start Required Services

```bash
# Terminal 1: Start Laravel development server
cd laravel && php artisan serve

# Terminal 2: Start queue worker (processes broadcast events)
cd laravel && php artisan queue:work --queue=broadcast

# Terminal 3: Start Redis (if not running as service)
redis-server

# Terminal 4: Start GO long-polling service
cd go-service && go run main.go
```

### Step 5: How It Works

1. **User opens chat** (`/chat`)
   - Frontend calls `/chat/state`
   - Receives: offset with -10 margin, JWT token, and pre-built updates URL
   - Immediately starts long polling from this offset
   - First poll returns the last ~10 messages (messages between offset-10 and current)

2. **User sends message** (`/chat/send`)
   - Laravel stores event in `long_polling_events` table
   - Publishes notification to Redis channel
   - GO service receives Redis notification
   - GO service fetches event from Laravel
   - GO service delivers event to waiting clients

3. **Frontend polls for updates**
   - Calls GO service: `${get_updates_url}&offset=${currentOffset}&token=${token}`
   - GO service holds connection until new events or timeout
   - Returns new events when available
   - Frontend updates offset and continues polling

### Available Methods

The package provides additional methods for advanced use cases:

```php
use Levskiy0\LongPolling\Facades\LongPolling;

// Get last offset for a channel
$offset = LongPolling::getLastOffset('chat');

// Get last N events (for initial load)
$events = LongPolling::getLastEvents('chat', 10);

// Get events from specific offset (for manual polling)
$updates = LongPolling::getUpdates('chat', fromOffset: 100, limit: 50);

// Get JWT token for GO service authentication
$driver = app(\Levskiy0\LongPolling\Contracts\LongPollingContract::class);
$token = $driver->getToken('chat');
```

### Production Considerations

- **Queue Workers**: Use Supervisor to keep queue workers running
- **Redis**: Ensure Redis is persistent and monitored
- **GO Service**: Run behind reverse proxy (Nginx) with SSL
- **Rate Limiting**: Add rate limiting to `/chat/send` endpoint
- **Authentication**: Ensure all routes are properly authenticated
- **Scaling**: GO service can be scaled horizontally, Redis pub/sub will distribute to all instances

## API Reference

### LongPolling Facade

#### `broadcast(int|string $channelId, array $payload): void`

Broadcasts an event to a specific channel. The event will be queued and processed asynchronously.

**Parameters:**
- `$channelId`: Channel identifier (user ID, room ID, etc.)
- `$payload`: Event data as an associative array

### Internal Endpoint

#### `GET /api/long-polling/getEvents`

Internal endpoint used by the Go service to fetch events.

**Query Parameters:**
- `channel_id` (required): Channel to fetch events for
- `secret` (required): Shared secret for authentication
- `offset` (optional): Last event ID (default: 0)
- `limit` (optional): Max events to return (default: 100)

**Response:**
```json
{
  "events": [
    {
      "id": 1,
      "event": {"type": "message", "data": "..."},
      "created_at": 1699876543
    }
  ],
  "count": 1
}
```

## Database Schema

### `long_polling_events` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `channel_id` | string | Channel identifier (indexed) |
| `event` | longtext | JSON-encoded event data |
| `created_at` | timestamp | Event creation time |

Indexes:
- `channel_id` - For efficient channel lookups
- `(channel_id, id)` - For efficient offset-based queries

## Testing

...


## License

MIT
