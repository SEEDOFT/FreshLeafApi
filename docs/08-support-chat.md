# Support Chat

## Overview

Real-time customer support chat system connecting users with admins. Supports text messages, file attachments, and image viewing.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **Support Chat Controller** | `app/Http/Controllers/Api/User/SupportChatController.php` | Ticket/message handling |
| **Support Chat Wrapper** | `app/Filament/Admin/Pages/SupportChat.php` | Filament page entry point |
| **Livewire Component** | `app/Livewire/SupportChat.php` | Centralized chat logic |
| **Blade View** | `resources/views/livewire/support-chat.blade.php` | Reusable chat interface |
| **Alpine Component** | `resources/js/livewire/support-chat.js` | UI logic & JavaScript polling |
| **Support Ticket Model** | `app/Models/SupportTicket.php` | Ticket entity |
| **Support Message Model** | `app/Models/SupportMessage.php` | Message entity |
| **PushNotification** | `app/Notifications/PushNotification.php` | Base FCM notification class |

### Centralized Architecture

The Support Chat system has been refactored for maximum reusability:
1.  **Filament Page**: Acts as a thin wrapper and sidebar entry point.
2.  **Standalone Livewire Component**: Encapsulates all chat state, message processing, and Echo listeners. This allows the chat to be easily embedded in other parts of the system in the future.
3.  **JavaScript Polling**: Replaces `wire:poll` with a smooth Alpine.js `setInterval` mechanism (`startPolling`/`stopPolling`) to eliminate UI flickering during ticket list refreshes.

### Events (Real-time)

| Event | Path | Description |
|-------|------|-------------|
| SupportMessageSent | `app/Events/SupportMessageSent.php` | New message sent |
| SupportTyping | `app/Events/SupportTyping.php` | Typing indicator |
| NewSupportTicket | `app/Events/NewSupportTicket.php` | New ticket created |

### Notifications

| Notification | Path | Description |
|--------------|------|-------------|
| NewSupportMessageNotification | `app/Notifications/NewSupportMessageNotification.php` | FCM push to user |
| NewSupportTicketNotification | `app/Notifications/NewSupportTicketNotification.php` | FCM push to admins |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/support/ticket` | Get user's active ticket |
| GET | `/api/v1/support/unread-count` | Get unread message count |
| POST | `/api/v1/support/messages` | Send message (with optional file) |
| GET | `/api/v1/support/messages` | Get message history |
| POST | `/api/v1/support/typing` | Send typing indicator |

## File Attachments

### Sending Files

Users can attach files (images, documents) when sending messages:

```dart
// Flutter: lib/app/modules/support_chat/controllers/support_chat_controller.dart
// Using image_picker to capture from camera or gallery
final picker = ImagePicker();
final pickedFile = await picker.pickImage(
  source: source, // ImageSource.camera or ImageSource.gallery
);

if (pickedFile != null) {
  final formData = FormData.fromMap({
    'message': messageText,
    'file': await MultipartFile.fromFile(pickedFile.path),
  });
  
  await _apiClient.postMultipart(
    ApiEndpoints.supportSendMessage,
    data: formData,
  );
}
```

### Receiving Files

Files are served from Laravel storage:

```dart
// Flutter: Construct file URL
final fileUrl = '${AppConfig.apiUrl.replaceAll('/api/v1', '')}/storage/${message.filePath}';

// Download and share
await Share.shareXFiles([XFile(filePath)], text: 'Shared from FreshLeaf');
```

## Image Viewer

Full-screen image viewer with pinch-to-zoom support:

```dart
// Flutter: lib/app/modules/support_chat/views/support_chat_view.dart
Get.to(
  () => Scaffold(
    backgroundColor: Colors.black,
    appBar: AppBar(backgroundColor: Colors.transparent),
    body: InteractiveViewer(
      minScale: 1.0,
      maxScale: 4.0,
      child: Center(
        child: Image.file(File(imagePath)),
      ),
    ),
  ),
);
```

## Real-time Flow

### User → Admin

1. User sends message via API (with optional file attachment)
2. Message saved to database (file stored in `storage/app/public/attachments/`)
3. `SupportMessageSent` event broadcast to:
   - `support.ticket.{ticketId}` - For the specific conversation
   - `support.admin` - For all admins
4. FCM notification sent to admins

### Admin → User

1. Admin sends message via Filament page (with optional file attachment)
2. Message saved to database
3. `SupportMessageSent` event broadcast to:
   - `support.ticket.{ticketId}` - For user to receive
4. FCM notification sent to user

### Typing Indicators

1. User/Admin types in input
2. `SupportTyping` event sent to both channels
3. Receiving end shows typing animation (3 second timeout)
4. **Note:** Scrolling to bottom on typing events is disabled to prevent UX issues when user is reading older messages

## WebSocket Channels

| Channel | Authorization |
|---------|---------------|
| `support.ticket.{ticketId}` | Ticket owner OR Admin |
| `support.admin` | Admin only |
| `/api/v1/broadcasting/auth` | Laravel BroadcastController for Pusher authentication |

## Database Tables

### support_tickets
Support conversations:
- user_id (owner)
- status (open, resolved)
- created_at, updated_at

### support_messages
Chat messages:
- support_ticket_id
- sender_type (user/admin)
- sender_id
- message (text)
- file_path (optional attachment)
- is_read (read status)

## Admin Panel

Admin accesses support chat via Filament:
- URL: `/admin/support-chat`
- Real-time updates via Livewire echo listeners
- File attachments supported (upload via file picker)
- Can resolve tickets

## Debugging FCM Notifications

If push notifications are not working:

1. **Check Laravel logs**: `storage/logs/laravel.log` - Look for `[PushNotification]` entries showing token retrieval
2. **Check Flutter logs**: Look for `[NotificationService]` entries showing FCM token retrieval and upload
3. **Verify device token**: Query `user_devices` table in database
4. **Test via Laravel tinker**:
   ```php
   php artisan tinker --execute 'Notification::send($user, new \App\Notifications\NewSupportMessageNotification($ticket));'
   ```

## Related Files

- `routes/channels.php` - Channel authorization
- `app/Http/Controllers/BroadcastController.php` - WebSocket auth endpoint
- `resources/views/filament/pages/support-chat.blade.php` - Chat UI
- `app/Notifications/PushNotification.php` - Base FCM class
- `lib/app/modules/support_chat/` - Flutter support chat module