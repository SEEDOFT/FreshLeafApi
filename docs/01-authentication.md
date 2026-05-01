# Authentication System

## Overview

FreshLeaf uses Laravel Sanctum for token-based API authentication. The system supports three user types with separate authentication flows.

## User Types

| Type ID | Code | Description |
|---------|------|-------------|
| 1 | ADMIN | Platform administrators |
| 2 | VENDOR | Product sellers |
| 3 | USER | Consumer/buyer |

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **User Auth Controller** | `app/Http/Controllers/Api/User/AuthController.php` | User register/login/logout |
| **Admin Auth Controller** | `app/Http/Controllers/Api/Admin/AuthController.php` | Admin login/logout |
| **Vendor Auth Controller** | `app/Http/Controllers/Api/Vendor/AuthController.php` | Vendor login/logout |
| **PIN Controller** | `app/Http/Controllers/Api/User/UserPinController.php` | PIN security management |
| **User Model** | `app/Models/User.php` | User entity with type relationships |

## API Endpoints

### User Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|--------------|------|
| POST | `/api/v1/user/auth/register` | Register new user | No |
| POST | `/api/v1/user/auth/login` | Login user | No |
| POST | `/api/v1/user/auth/logout` | Logout user | Yes |
| POST | `/api/v1/user/auth/password/verify` | Verify password | Yes |
| POST | `/api/v1/user/auth/password/update` | Update password | Yes |

### Admin Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|--------------|------|
| POST | `/api/v1/admin/auth/login` | Login admin | No |
| POST | `/api/v1/admin/auth/logout` | Logout admin | Yes |
| POST | `/api/v1/admin/auth/password/verify` | Verify password | Yes |
| POST | `/api/v1/admin/auth/password/update` | Update password | Yes |

### Vendor Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|--------------|------|
| POST | `/api/v1/vendor/auth/register` | Register new vendor | No |
| POST | `/api/v1/vendor/auth/login` | Login vendor | No |

## PIN Security

Users can set a PIN for quick order verification without password.

| Method | Endpoint | Description |
|--------|----------|--------------|
| POST | `/api/v1/user/pin/set` | Set new PIN (requires password) |
| POST | `/api/v1/user/pin/update` | Update existing PIN |
| POST | `/api/v1/user/pin/reset` | Reset PIN (requires password) |
| POST | `/api/v1/user/pin/verify` | Verify PIN |

### PIN Flow

```
1. User has set_pin = false:
   - Must verify password before setting PIN
   
2. User has set_pin = true:
   - Can update PIN using current PIN (no password needed)
   
3. Forgot PIN:
   - Can reset by verifying password
```

## Authentication Middleware

The system uses custom middleware to enforce user type:

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'active.type:user'])->group(...);
Route::middleware(['auth:sanctum', 'active.type:admin'])->group(...);
Route::middleware(['auth:sanctum', 'active.type:vendor'])->group(...);
```

### Active Type Middleware

Location: `app/Http/Middleware/ActiveType.php`

Checks that:
1. User is authenticated
2. User's account status is ACTIVE
3. User type matches the required type

## Token Management

- Tokens are issued via Laravel Sanctum
- Token expiration: configured in `config/sanctum.php`
- Devices (FCM tokens) can be tracked for push notifications

## Related Files

- `config/sanctum.php` - Sanctum configuration
- `app/Models/User.php` - User model with type relationships
- `app/Http/Middleware/ActiveType.php` - Active user type check
- `database/migrations/*_create_users_table.php` - User table schema