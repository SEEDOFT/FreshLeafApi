# Payment Methods

## Overview

User saved payment methods and admin-managed payment method types.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **User Payment Method Controller** | `app/Http/Controllers/Api/User/PaymentMethodController.php` | User payment methods |
| **Payment Method Type Controller** | `app/Http/Controllers/Api/User/PaymentMethodTypeController.php` | Type listing |
| **Admin Payment Method Type Controller** | `app/Http/Controllers/Api/Admin/PaymentMethodTypeController.php` | Admin type management |
| **Payment Method Model** | `app/Models/PaymentMethod.php` | Saved payment method |
| **Payment Method Type Model** | `app/Models/PaymentMethodType.php` | Payment type |
| **Payment Method Status Model** | `app/Models/PaymentMethodStatus.php` | Status definitions |

## API Endpoints

### User Payment Methods

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/user/payment-methods` | List saved payment methods |
| GET | `/api/v1/user/payment-methods/{id}` | Get payment method |
| POST | `/api/v1/user/payment-methods` | Add new payment method |
| PUT | `/api/v1/user/payment-methods/{id}` | Update payment method |
| PATCH | `/api/v1/user/payment-methods/{id}` | Partial update |
| DELETE | `/api/v1/user/payment-methods/{id}` | Delete payment method |

### Payment Method Types

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/user/payment-method-types` | List available types |
| GET | `/api/v1/user/payment-method-types/{id}` | Get type details |

### Admin Payment Method Types

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/admin/payment-method-types` | List all types |
| POST | `/api/v1/admin/payment-method-types` | Create type |
| PUT | `/api/v1/admin/payment-method-types/{id}` | Update type |
| DELETE | `/api/v1/admin/payment-method-types/{id}` | Delete type |

## Database Tables

### payment_methods
User saved payment methods:
- user_id (owner)
- payment_method_type_id
- payment_method_status_id
- is_default
- Type-specific fields (card number last 4, bank account, etc.)

### payment_method_types
Payment method type definitions:
- wallet (internal)
- credit_debit (cards)
- ABA (ABA Pay)
- ACLEDA (ACLEDA OnePay)
- COD (Cash on Delivery)

### payment_method_statuses
Status lookup: active, inactive

## Payment Flow

```
1. User adds payment method
2. Payment method saved with ACTIVE status
3. User can set as default
4. At checkout, user selects payment method
5. Payment processed based on type
```

## Related Files

- `app/Notifications/Order/NewOrderNotification.php` - Payment triggers
- `app/Notifications/Order/OrderStatusUpdatedNotification.php`