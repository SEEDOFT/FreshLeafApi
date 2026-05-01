# Orders System

## Overview

Order management system for the marketplace, including order creation, status tracking, and commission calculation for admins.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **Order Model** | `app/Models/Order.php` | Order entity |
| **Order Item Model** | `app/Models/OrderItem.php` | Order line items |
| **Order Status Model** | `app/Models/OrderStatus.php` | Status definitions |
| **Order Type Model** | `app/Models/OrderType.php` | Order type definitions |
| **Order Status History** | `app/Models/OrderStatusHistory.php` | Status change tracking |

## Database Tables

### orders
Main order table:
- user_id (buyer)
- address_id (delivery address)
- order_type_id
- order_status_id
- payment_status_id
- delivery_date, delivery_slot
- subtotal, commission_amount, total
- notes

### order_items
Order line items:
- product_id, variant_id
- quantity, unit_price
- commission_amount (admin commission)

### order_statuses
Status lookup table:
- pending, confirmed, preparing, delivering, delivered, cancelled

### order_status_histories
Audit trail of status changes

### product_substitutions
Allowed substitutions for out-of-stock items

## Order Flow

```
1. User creates order (via Flutter app)
2. Order status: PENDING
3. Admin/vendor confirms → CONFIRMED
4. Vendor prepares → PREPARING
5. Delivery in progress → DELIVERING
6. Completed → DELIVERED
```

## Commission System

Admin earns commission on each completed sale:
- commission_amount stored in order_item
- Calculated from vendor's product price

## Order Statuses

| Status | Description |
|--------|-------------|
| PENDING | Awaiting confirmation |
| CONFIRMED | Order confirmed |
| PREPARING | Being prepared |
| DELIVERING | Out for delivery |
| DELIVERED | Completed |
| CANCELLED | Cancelled |

## Related Files

- `app/Filament/Resources/Orders/` - Admin order management
- `app/Filament/Widgets/AdminCommissionWidget.php` - Commission tracking