# User Management

## Overview

Manages consumer user profiles, addresses, payment methods, and device registrations for push notifications.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **Profile Controller** | `app/Http/Controllers/Api/User/ProfileController.php` | User profile CRUD |
| **Address Controller** | `app/Http/Controllers/Api/User/AddressController.php` | Shipping addresses |
| **Payment Method Controller** | `app/Http/Controllers/Api/User/PaymentMethodController.php` | Saved payment methods |
| **Device Controller** | `app/Http/Controllers/Api/User/DeviceController.php` | FCM token registration |
| **User Profile Model** | `app/Models/UserProfile.php` | Extended user info |
| **Address Model** | `app/Models/Address.php` | Address entity |
| **Payment Method Model** | `app/Models/PaymentMethod.php` | Payment method entity |
| **User Device Model** | `app/Models/UserDevice.php` | FCM device tokens |

## API Endpoints

### Profile

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/user/profile` | Get current user profile |
| PUT | `/api/v1/user/profile` | Full profile replacement |
| PATCH | `/api/v1/user/profile` | Partial profile update |
| DELETE | `/api/v1/user/profile` | Delete account |

**Note:** For file uploads (e.g., profile image), use `POST` with `_method: PUT` or `_method: PATCH` for multipart/form-data.

### Addresses

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/user/addresses` | List all addresses |
| GET | `/api/v1/user/addresses/{id}` | Get address details |
| POST | `/api/v1/user/addresses` | Create new address |
| PUT | `/api/v1/user/addresses/{id}` | Full address replacement |
| PATCH | `/api/v1/user/addresses/{id}` | Partial address update |
| DELETE | `/api/v1/user/addresses/{id}` | Delete address |

### Payment Methods

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/user/payment-methods` | List saved payment methods |
| GET | `/api/v1/user/payment-methods/{id}` | Get payment method details |
| POST | `/api/v1/user/payment-methods` | Add new payment method |
| PUT | `/api/v1/user/payment-methods/{id}` | Full update payment method |
| PATCH | `/api/v1/user/payment-methods/{id}` | Partial update payment method |
| DELETE | `/api/v1/user/payment-methods/{id}` | Delete payment method |

### Devices (FCM Tokens)

| Method | Endpoint | Description |
|--------|----------|--------------|
| POST | `/api/v1/user/devices` | Register FCM device token |
| DELETE | `/api/v1/user/devices/{token}` | Remove device token |

## Database Tables

### user_profiles
Extended user information including locale, PIN status, date of birth, gender.

### addresses
User shipping addresses with GPS coordinates (lat/lng).

### payment_methods
Saved payment methods (cards, bank accounts).

### user_devices
FCM tokens for push notifications.

## Related Files

- `app/Models/UserProfile.php`
- `app/Models/Address.php`
- `app/Models/PaymentMethod.php`
- `app/Models/UserDevice.php`
- `app/Notifications/*.php` - Push notification classes