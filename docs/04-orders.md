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
- vendor_inventory_id (Link to specific vendor stock)
- quantity
- subtotal
- Snapshot fields: product_name_snapshot, unit_snapshot, unit_price_snapshot
- commission_amount (admin commission)
- vendor_net_amount (amount vendor receives)

### order_statuses
Status lookup table:
- pending, confirmed, preparing, delivering, delivered, cancelled

### order_status_histories
Audit trail of status changes

## Order Flow

```
1. User adds Vendor Inventory items to Cart.
2. User checks out (via Flutter app) generating an Order.
3. Order status: PENDING
4. Admin/vendor confirms → CONFIRMED
5. Vendor prepares → PREPARING
6. Delivery in progress → DELIVERING
7. Completed → DELIVERED
```

## Commission System

Admin earns commission on each completed sale:
- commission_amount stored in order_item
- Calculated from vendor's product price (using global `commission_rate_percentage` setting)

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