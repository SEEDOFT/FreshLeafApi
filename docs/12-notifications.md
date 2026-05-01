# Push Notifications

## Overview

FreshLeaf uses Firebase Cloud Messaging (FCM) for push notifications to Flutter mobile app.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **Base Push Notification** | `app/Notifications/PushNotification.php` | FCM base class |
| **New Order Notification** | `app/Notifications/Order/NewOrderNotification.php` | New order created |
| **Order Status Notification** | `app/Notifications/Order/OrderStatusUpdatedNotification.php` | Order status change |
| **Support Message Notification** | `app/Notifications/NewSupportMessageNotification.php` | New support message |
| **Support Ticket Notification** | `app/Notifications/NewSupportTicketNotification.php` | New support ticket |

### FCM Channel

The system uses a custom FCM channel for Laravel notifications.

## Notification Flow

### 1. Device Registration

User registers FCM token:

```php
POST /api/v1/user/devices
{
  "device_token": "fcm_token_here",
  "device_type": "android"
}
```

### 2. Notification Sending

```php
// Send notification
Notification::sendNow($user, new NewOrderNotification($order));
```

### 3. FCM Delivery

The `FcmChannel` handles:
- Token validation
- Message formatting
- Retry logic

## Notification Types

### Order Notifications

| Notification | Trigger | Recipients |
|--------------|---------|------------|
| NewOrderNotification | New order created | Admin, Vendor |
| OrderStatusUpdatedNotification | Order status changed | User |

### Support Notifications

| Notification | Trigger | Recipients |
|--------------|---------|------------|
| NewSupportMessageNotification | Admin replies to user | User |
| NewSupportTicketNotification | New support ticket | Admin |

### Marketing Notifications (Future)

| Notification | Trigger | Recipients |
|--------------|---------|-------------|
| PromotionNotification | Promotional campaigns | Users |

## Database Tables

### user_devices
FCM device tokens:
- user_id (owner)
- device_token
- device_type
- is_active

## Flutter Integration

### Notification Service

Location: `fresh_leaf/lib/core/services/notification_service.dart`

Handles:
- FCM token management
- Foreground notification handling
- Background notification handling

### Data Payload

```json
{
  "notification": {
    "title": "New Order",
    "body": "You have a new order from John Doe"
  },
  "data": {
    "type": "order",
    "order_id": "123"
  }
}
```

## Configuration

```env
# .env
FCM_SERVER_KEY=your_fcm_server_key
```

## Related Files

- `config/services.php` - FCM configuration
- `app/Models/UserDevice.php` - Device token model
- `app/Http/Controllers/Api/User/DeviceController.php` - Device management