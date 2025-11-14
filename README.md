# Laravel Long-Polling Package

Laravel package for long-polling functionality with Go service integration.

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
php artisan vendor:publish --tag=longpolly-config
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

The configuration file is located at `config/longpolly.php`:

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
use Levskiy0\LongPolling\Contracts\LongPollingDriver;

$driver = app(LongPollingDriver::class);
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

## API Reference

### LongPolly Facade

#### `broadcast(int|string $channelId, array $payload): void`

Broadcasts an event to a specific channel. The event will be queued and processed asynchronously.

**Parameters:**
- `$channelId`: Channel identifier (user ID, room ID, etc.)
- `$payload`: Event data as an associative array

### Internal Endpoint

#### `GET /api/longpolly/getEvents`

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

Run the package tests:

```bash
cd packages/levskiy0/laravel-long-polling
composer install
vendor/bin/phpunit
```

Or from your Laravel application:

```bash
php artisan test --filter=LongPolling
```

## Architecture

1. **Application calls `LongPolly::broadcast()`**
   - Event is dispatched to the broadcast queue

2. **Queue worker processes the job**
   - Event is stored in database
   - Notification is published to Redis

3. **Go service receives Redis notification**
   - Wakes up waiting long-poll connections
   - Fetches new events from Laravel via `/getEvents`

4. **Client receives events**
   - Processes events and updates offset
   - Continues polling

## License

MIT
