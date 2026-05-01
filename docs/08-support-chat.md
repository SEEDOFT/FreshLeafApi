# Support Chat

## Overview

Real-time customer support chat system connecting users with admins. Supports text messages and file attachments.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **Support Chat Controller** | `app/Http/Controllers/Api/User/SupportChatController.php` | Ticket/message handling |
| **Support Chat Page** | `app/Filament/Pages/SupportChat.php` | Admin chat interface |
| **Support Ticket Model** | `app/Models/SupportTicket.php` | Ticket entity |
| **Support Message Model** | `app/Models/SupportMessage.php` | Message entity |

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
| GET | `/api/v1/user/support/ticket` | Get user's active ticket |
| GET | `/api/v1/user/support/unread-count` | Get unread message count |
| POST | `/api/v1/user/support/messages` | Send message |
| GET | `/api/v1/user/support/messages` | Get message history |
| POST | `/api/v1/user/support/typing` | Send typing indicator |

## Real-time Flow

### User → Admin

1. User sends message via API
2. Message saved to database
3. `SupportMessageSent` event broadcast to:
   - `support.ticket.{ticketId}` - For the specific conversation
   - `support.admin` - For all admins
4. FCM notification sent to admins

### Admin → User

1. Admin sends message via Filament page
2. Message saved to database
3. `SupportMessageSent` event broadcast to:
   - `support.ticket.{ticketId}` - For user to receive
4. FCM notification sent to user

### Typing Indicators

1. User/Admin types in input
2. `SupportTyping` event sent to both channels
3. Receiving end shows typing animation (3 second timeout)

## WebSocket Channels

| Channel | Authorization |
|---------|---------------|
| `support.ticket.{ticketId}` | Ticket owner OR Admin |
| `support.admin` | Admin only |

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
- File attachments supported
- Can resolve tickets

## Related Files

- `routes/channels.php` - Channel authorization
- `resources/views/filament/pages/support-chat.blade.php` - Chat UI
- `app/Notifications/PushNotification.php` - Base FCM class