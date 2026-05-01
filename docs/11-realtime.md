# Real-time System

## Overview

FreshLeaf uses Laravel Reverb for WebSocket real-time communication. This enables live features like AI chat streaming, support chat, and typing indicators.

## Architecture

```
┌─────────────┐     WebSocket      ┌─────────────┐
│  Flutter    │ ◄─────────────────►│   Reverb    │
│    App      │                    │  (Server)   │
└─────────────┘                    └──────────────┘
       │                                   │
       │ HTTP                              │
       ▼                                   ▼
┌─────────────┐                    ┌──────────────┐
│   Laravel   │ ◄─────────────────►│   Channel   │
│     API     │                    │  Auth       │
└─────────────┘                    └──────────────┘
```

## Components

### Reverb Configuration

```env
# .env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=freshleaf_app
REVERB_APP_KEY=freshleaf_key_123456789
REVERB_APP_SECRET=freshleaf_secret_123456789
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Channel Definitions

Location: `routes/channels.php`

| Channel | Authorization | Description |
|---------|---------------|-------------|
| `ai-chat.{userId}.{sessionId}` | Session owner only | AI chat |
| `support.ticket.{ticketId}` | Ticket owner OR Admin | Support chat |
| `support.admin` | Admin only | Admin notifications |

### Broadcasting Auth

Endpoint: `POST /api/v1/broadcasting/auth`

Required headers:
```
Authorization: Bearer {token}
Content-Type: application/x-www-form-urlencoded
```

Body:
```json
{
  "socket_id": "socket_id_here",
  "channel_name": "private-channel-name"
}
```

## Events

### AI Chat Events

| Event | Broadcast To | Description |
|-------|--------------|-------------|
| `AiMessageStarted` | `ai-chat.{userId}.{sessionId}` | AI starts response |
| `AiMessageChunk` | `ai-chat.{userId}.{sessionId}` | Streaming text |
| `AiMessageCompleted` | `ai-chat.{userId}.{sessionId}` | Response complete |
| `AiMessageFailed` | `ai-chat.{userId}.{sessionId}` | Error occurred |

### Support Chat Events

| Event | Broadcast To | Description |
|-------|--------------|-------------|
| `SupportMessageSent` | `support.ticket.{id}`, `support.admin` | New message |
| `SupportTyping` | `support.ticket.{id}`, `support.admin` | Typing indicator |
| `NewSupportTicket` | `support.admin` | New ticket created |

## Frontend Integration

### Laravel (Backend)

```php
// Broadcast event
broadcast(new SupportMessageSent($message))->toOthers();
```

### Flutter App

```dart
// Connect to WebSocket
final channel = WebSocketChannel.connect(uri);

// Subscribe to channel
channel.sink.add(jsonEncode({
  'event': 'pusher:subscribe',
  'data': {
    'channel': 'private-support.ticket.$ticketId',
    'auth': authToken,
  },
}));

// Listen for events
channel.stream.listen((message) {
  // Handle incoming events
});
```

### Filament (Admin)

Uses Laravel Echo via Livewire's `getListeners()`:

```php
public function getListeners(): array
{
    return [
        'echo-private:support.admin,SupportMessageSent' => 'handleIncomingMessage',
    ];
}
```

## Configuration Files

| File | Purpose |
|------|---------|
| `.env` | Reverb connection settings |
| `config/reverb.php` | Reverb configuration |
| `config/broadcasting.php` | Broadcast driver config |
| `routes/channels.php` | Channel authorization |
| `resources/js/bootstrap.js` | Echo client initialization |

## Starting Reverb

```bash
# Start Reverb server
php artisan reverb:start

# Or run all dev services
composer run dev
```

## Troubleshooting

### Connection Issues

1. Check Reverb is running: `php artisan reverb:info`
2. Verify .env settings
3. Check firewall allows WebSocket port

### Channel Auth Failures

1. Verify user is authenticated
2. Check channel authorization in `routes/channels.php`
3. Ensure correct guard is being used

### Events Not Received

1. Check browser console for Echo errors
2. Verify subscription succeeded
3. Check event name matches exactly