# API Endpoints Reference

## Base URL

```
http://localhost:8000/api/v1
```

## Authentication

All authenticated endpoints require:
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

## Endpoints by Feature

### Categories

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/categories` | List all categories |
| GET | `/categories/{slug}` | Get category by slug |

---

### Authentication

#### User Auth

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/user/auth/register` | Register user | No |
| POST | `/user/auth/login` | Login | No |
| POST | `/user/auth/logout` | Logout | Yes |
| POST | `/user/auth/password/verify` | Verify password | Yes |
| POST | `/user/auth/password/update` | Update password | Yes |

#### PIN Security

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/user/pin/set` | Set PIN | Yes |
| POST | `/user/pin/update` | Update PIN | Yes |
| POST | `/user/pin/reset` | Reset PIN | Yes |
| POST | `/user/pin/verify` | Verify PIN | Yes |

#### Admin Auth

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/auth/login` | Login admin | No |
| POST | `/admin/auth/logout` | Logout | Yes |

#### Vendor Auth

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/vendor/auth/register` | Register vendor | No |
| POST | `/vendor/auth/login` | Login vendor | No |

---

### User Profile

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/profile` | Get profile | Yes |
| PUT | `/user/profile` | Full update | Yes |
| PATCH | `/user/profile` | Partial update | Yes |
| DELETE | `/user/profile` | Delete account | Yes |

### User Addresses

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/addresses` | List addresses | Yes |
| POST | `/user/addresses` | Create address | Yes |
| GET | `/user/addresses/{id}` | Get address | Yes |
| PUT | `/user/addresses/{id}` | Full update | Yes |
| PATCH | `/user/addresses/{id}` | Partial update | Yes |
| DELETE | `/user/addresses/{id}` | Delete address | Yes |

### User Products

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/products` | List products | Yes |
| GET | `/user/products/{id}` | Get product | Yes |

### User Wallets

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/wallets` | List wallets | Yes |
| GET | `/user/wallets/{id}` | Get wallet | Yes |
| GET | `/user/wallets/{id}/histories` | Get history | Yes |

### Wallet Transactions

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/wallet-transactions` | List transactions | Yes |
| GET | `/user/wallet-transactions/{id}` | Get transaction | Yes |
| POST | `/user/wallet-transactions` | Create transaction | Yes |
| PATCH | `/user/wallet-transactions/{id}` | Update transaction | Yes |
| DELETE | `/user/wallet-transactions/{id}` | Delete transaction | Yes |

### Payment Methods

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/payment-methods` | List payment methods | Yes |
| POST | `/user/payment-methods` | Add payment method | Yes |
| GET | `/user/payment-methods/{id}` | Get payment method | Yes |
| PUT/PATCH/DELETE | `/user/payment-methods/{id}` | Manage payment method | Yes |

### Devices

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/user/devices` | Register FCM token | Yes |
| DELETE | `/user/devices/{token}` | Remove token | Yes |

---

### AI Chat

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/ai/chat/sessions` | Create session | Yes |
| POST | `/ai/chat/messages` | Send message | Yes |
| POST | `/ai/chat/history` | Get history | Yes |

---

### Support Chat

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/support/ticket` | Get active ticket | Yes |
| GET | `/user/support/unread-count` | Get unread count | Yes |
| POST | `/user/support/messages` | Send message | Yes |
| GET | `/user/support/messages` | Get messages | Yes |
| POST | `/user/support/typing` | Send typing | Yes |

---

### Admin Products

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/products` | List all products | Yes |
| GET | `/admin/products/{id}` | Get product | Yes |

### Admin Vendors

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/vendors/pending` | List pending | Yes |
| GET | `/admin/vendors/pending/{id}` | View pending | Yes |
| PATCH | `/admin/vendors/pending/{id}` | Approve/reject | Yes |

### Admin Wallets

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/wallets` | List all wallets | Yes |
| GET | `/admin/wallets/{id}` | Get wallet | Yes |
| GET | `/admin/wallets/{id}/histories` | Get history | Yes |

### Admin Payment Types

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/payment-method-types` | List types | Yes |
| POST | `/admin/payment-method-types` | Create type | Yes |
| PUT/PATCH/DELETE | `/admin/payment-method-types/{id}` | Manage type | Yes |

---

### Vendor Profile

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/vendor/profile` | Get profile | Yes |
| PATCH | `/vendor/profile` | Update profile | Yes |

### Vendor Products

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/vendor/products` | List own products | Yes |
| GET | `/vendor/products/{id}` | Get product | Yes |

### Vendor Addresses

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/vendor/addresses` | List addresses | Yes |
| POST | `/vendor/addresses` | Create address | Yes |
| GET/PUT/PATCH/DELETE | `/vendor/addresses/{id}` | Manage address | Yes |

### Vendor Wallets

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/vendor/wallets` | List wallets | Yes |
| GET | `/vendor/wallets/{id}` | Get wallet | Yes |
| GET | `/vendor/wallets/{id}/histories` | Get history | Yes |

---

### Broadcasting

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/broadcasting/auth` | Channel auth | Yes |

---

## Response Format

All responses follow this structure:

```json
{
  "status": {
    "code": "200",
    "success": true,
    "message": "Success message"
  },
  "data": { ... }
}
```

Error response:

```json
{
  "status": {
    "code": "400",
    "success": false,
    "message": "Error message"
  },
  "data": []
}
```