# FreshLeaf API Documentation

Welcome to the FreshLeaf API backend documentation. This folder contains detailed information about each feature in the system.

## Table of Contents

### Core Features
- [01. Authentication](01-authentication.md) - User, Admin, Vendor auth + PIN security
- [02. User Management](02-user-management.md) - Profiles, Addresses, Devices
- [03. Products & Categories](03-products-categories.md) - Product catalog
- [04. Orders](04-orders.md) - Order system
- [05. Wallet System](05-wallet.md) - Digital wallets (KHR/USD)
- [06. Payment Methods](06-payment-methods.md) - Saved payment methods
- [07. AI Assistant](07-ai-chat.md) - AI chat with real-time streaming
- [08. Support Chat](08-support-chat.md) - Customer support system
- [09. Vendor Management](09-vendor-management.md) - Vendor approval & profiles

### System Features
- [10. Admin Panel](10-admin-panel.md) - Filament admin documentation
- [11. Real-time System](11-realtime.md) - WebSocket/Reverb configuration
- [12. Notifications](12-notifications.md) - Push notifications (FCM)
- [13. API Endpoints](13-api-endpoints.md) - Complete API reference

## Quick Links

| Feature | Description |
|---------|-------------|
| **API Version** | `/api/v1/` |
| **Authentication** | Laravel Sanctum (token-based) |
| **Real-time** | Laravel Reverb (WebSocket) |
| **Admin Panel** | Filament (Laravel admin builder) |

## Architecture Overview

```
FreshLeafApi/
├── app/
│   ├── Http/Controllers/Api/     # API Controllers
│   ├── Services/Ai/              # AI Services
│   ├── Events/                   # Event classes
│   ├── Notifications/            # FCM Notifications
│   ├── Livewire/                 # Livewire Components
│   └── Filament/                 # Admin Panel
├── routes/
│   ├── api.php                   # REST API routes
│   ├── channels.php              # WebSocket channels
│   └── web.php                   # Web routes
└── docs/                         # This documentation
```

## Getting Started

1. **API Base URL**: `http://localhost:8000/api/v1`
2. **Broadcasting Auth**: `POST /api/v1/broadcasting/auth`
3. **WebSocket**: Laravel Reverb on port 8080

## Common Issues

See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for common issues and solutions.

---

For questions or contributions, refer to the main [README.md](../README.md)