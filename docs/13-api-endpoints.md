# API Endpoints Reference

## Base URL

```
http://localhost:8000/api/v1
```

## Authentication Strategy

The API uses **Token-Based Identification**. When an Admin, Vendor, or User logs in, they receive a Sanctum authentication token. Shared routes use this token to identify the user type and return appropriate data.

All authenticated endpoints require:
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

---

## Shared Endpoints (Admin, Vendor, User)

These endpoints are shared across all user types. The system identifies your account type from the token.

### Authentication (Shared)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/auth/logout` | Logout (Revoke token) | Yes |
| POST | `/auth/password/verify` | Verify current password | Yes |
| POST | `/auth/password/update` | Update password | Yes |

### Profile

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/profile` | Get profile info (Admin/Vendor/User) | Yes |
| PUT | `/profile` | Replace profile info | Yes |
| PATCH | `/profile` | Update profile info | Yes |
| DELETE | `/profile` | Delete account | Yes |

### Wallets

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/wallets` | List own wallets | Yes |
| GET | `/wallets/{id}` | Get wallet details | Yes |
| GET | `/wallets/{id}/histories` | Get wallet history | Yes |

### Products (Shared)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/products` | List active products. For Consumers, returns `VendorInventory` items for sale. | Yes |
| GET | `/products/{id}` | Get specific product/inventory details | Yes |

### Addresses (Shared - User & Vendor)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/addresses` | List addresses | Yes |
| POST | `/addresses` | Create address | Yes |
| GET | `/addresses/{id}` | Get address | Yes |
| PUT | `/addresses/{id}` | Full update | Yes |
| PATCH | `/addresses/{id}` | Partial update | Yes |
| DELETE | `/addresses/{id}` | Delete address | Yes |

---

## User-Specific Endpoints

### User Auth

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/auth/register` | Register consumer | No |
| POST | `/auth/login` | Login consumer | No |

### Shopping (Cart & Wishlist)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/cart` | Get active cart and items | Yes |
| POST | `/user/cart` | Add `vendor_inventory_id` to cart | Yes |
| PUT | `/user/cart/{itemId}` | Update cart item quantity | Yes |
| DELETE | `/user/cart/{itemId}` | Remove item from cart | Yes |
| GET | `/user/wishlist` | Get active wishlist and items | Yes |
| POST | `/user/wishlist/toggle` | Toggle `vendor_inventory_id` in wishlist | Yes |

### PIN Security

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/user/pin/set` | Set PIN | Yes |
| POST | `/user/pin/update` | Update PIN | Yes |
| POST | `/user/pin/reset` | Reset PIN | Yes |
| POST | `/user/pin/verify` | Verify PIN | Yes |

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

### AI Chat

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/user/ai/chat/sessions` | Create session | Yes |
| POST | `/user/ai/chat/messages` | Send message | Yes |
| POST | `/user/ai/chat/history` | Get history | Yes |

### Support Chat

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/support/ticket` | Get active ticket | Yes |
| GET | `/user/support/unread-count` | Get unread count | Yes |
| POST | `/user/support/messages` | Send message | Yes |
| GET | `/user/support/messages` | Get messages | Yes |
| POST | `/user/support/typing` | Send typing | Yes |

---

## Admin-Specific Endpoints

### Admin Auth

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/auth/login` | Login admin | No |

### Admin Vendors

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/vendors/pending` | List pending approvals | Yes |
| GET | `/admin/vendors/pending/{id}` | View pending details | Yes |
| PATCH | `/admin/vendors/pending/{id}` | Approve/reject vendor | Yes |

### Admin Payment Types

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/payment-method-types` | List types | Yes |
| POST | `/admin/payment-method-types` | Create type | Yes |
| PUT/PATCH/DELETE | `/admin/payment-method-types/{id}` | Manage type | Yes |

---

## Vendor-Specific Endpoints

### Vendor Auth

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/vendor/auth/register` | Register vendor | No |
| POST | `/vendor/auth/login` | Login vendor | No |

---

## Public Endpoints

### Categories

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/categories` | List all categories | No |
| GET | `/categories/{slug}` | Get category by slug | No |

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