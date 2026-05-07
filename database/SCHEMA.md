# Database Schema Reference - FreshLeaf API

This document provides a detailed overview of the database schema for the FreshLeaf API. The schema is organized into core domains to ensure scalability and clarity.

## Table of Contents
1. [User Domain](#1-user-domain)
2. [Product & Catalog Domain](#2-product--catalog-domain)
3. [Vendor Inventory Domain](#3-vendor-inventory-domain)
4. [Cart & Wishlist Domain](#4-cart--wishlist-domain)
5. [Order Domain](#5-order-domain)
6. [Wallet Domain](#6-wallet-domain)
7. [System Tables](#7-system-tables)

---

## 1. User Domain

### `users`
Core user account information.
- `id` (PK)
- `first_name` (string)
- `last_name` (string)
- `email` (string, unique, nullable)
- `phone_number` (string, unique)
- `password` (string, hashed)
- `user_type_id` (FK: `user_types`, nullable)
- `user_status_id` (FK: `user_statuses`, nullable)
- `email_verified_at` (timestamp, nullable)
- `remember_token` (string, nullable)
- `deleted_at` (timestamp, soft delete)
- `created_at`, `updated_at` (timestamps)

### `user_types`
Lookup for user roles/types.
- `id` (PK)
- `code` (string, unique) - e.g., 'consumer', 'admin', 'vendor'.
- `name` (string)

### `user_statuses`
Lookup for user account states.
- `id` (PK)
- `code` (string, unique) - e.g., 'active', 'suspended', 'pending'.
- `name` (string)

### `addresses`
User delivery addresses.
- `id` (PK)
- `user_id` (FK: `users`, cascade delete)
- `label` (string) - e.g., 'Home', 'Work'.
- `recipient_name` (string)
- `phone` (string)
- `address_line_1` (string)
- `address_line_2` (string, nullable)
- `city` (string)
- `province` (string)
- `postal_code` (string)

### `user_devices`
Tracks FCM tokens for push notifications.
- `id` (PK)
- `user_id` (FK: `users`, cascade delete)
- `device_token` (string, unique, index)
- `device_type` (string, nullable) - e.g., 'android', 'ios'
- `is_active` (boolean, default true)

---

## 2. Product & Catalog Domain

This domain acts as a "Dictionary" of available products.

### `product_categories`
- `id` (PK)
- `product_category_status_id` (FK: `product_category_statuses`)
- `name_en`, `name_km` (string)
- `description_en`, `description_km` (text, nullable)
- `image_url` (string, nullable)
- `slug` (string, unique, index)

### `product_category_statuses`
- `id` (PK)
- `code` (string, unique)
- `name` (string)

### `products`
The master dictionary definition of a product.
- `id` (PK)
- `product_category_id` (FK: `product_categories`)
- `product_type_id` (FK: `product_types`)
- `default_unit_id` (FK: `units`)
- `product_status_id` (FK: `product_statuses`)
- `name_en`, `name_km` (string)
- `slug` (string, unique, index)
- `description_en`, `description_km` (text, nullable)
- `nutrition_data` (json, nullable)
- `image_url` (string, nullable)
- `deleted_at` (timestamp, soft delete)

### `units`
- `id` (PK)
- `name` (string)
- `symbol` (string) - e.g., 'kg', 'g', 'pcs'.
- `conversion_to_base` (decimal)

---

## 3. Vendor Inventory Domain

Where vendors list physical stock for sale.

### `vendor_inventories`
Physical stock added by vendors.
- `id` (PK)
- `vendor_id` (FK: `users`)
- `product_id` (FK: `products`)
- `inventory_status_id` (FK: `vendor_inventory_statuses`)
- `price` (decimal)
- `stock_quantity` (decimal)
- `unit_id` (FK: `units`)
- `harvest_date` (date, nullable)
- `farm_location` (string, nullable)
- `province_of_origin` (string, nullable)
- `certification_type` (string, nullable)
- `packaging_type` (string, nullable)
- `shelf_life_days` (integer, nullable)
- `batch_images` (json, nullable)
- `deleted_at` (timestamp, soft delete)

### `vendor_inventory_statuses`
- `id` (PK)
- `code` (string, unique)
- `name` (string)

---

## 4. Cart & Wishlist Domain

### `user_carts`
- `id` (PK)
- `user_id` (FK: `users`, cascade delete)
- `user_cart_status_id` (FK: `user_cart_statuses`)
- `user_cart_type_id` (FK: `user_cart_types`)

### `user_cart_items`
- `id` (PK)
- `cart_id` (FK: `user_carts`, cascade delete)
- `vendor_inventory_id` (FK: `vendor_inventories`)
- `user_cart_item_status_id` (FK: `user_cart_item_statuses`)
- `user_cart_item_type_id` (FK: `user_cart_item_types`)
- `quantity` (decimal)
- `unit_price`, `subtotal` (decimals)

### `user_wishlists`
- `id` (PK)
- `user_id` (FK: `users`, cascade delete)
- `user_wishlist_status_id` (FK: `user_wishlist_statuses`)
- `user_wishlist_type_id` (FK: `user_wishlist_types`)

### `user_wishlist_items`
- `id` (PK)
- `user_wishlist_id` (FK: `user_wishlists`, cascade delete)
- `vendor_inventory_id` (FK: `vendor_inventories`)
- `user_wishlist_item_status_id` (FK: `user_wishlist_item_statuses`)
- `user_wishlist_item_type_id` (FK: `user_wishlist_item_types`)

---

## 5. Order Domain

### `orders`
- `id` (PK)
- `user_id` (buyer)
- `address_id` (delivery address)
- `order_type_id`
- `order_status_id`
- `payment_status_id`
- `delivery_date`, `delivery_slot`
- `subtotal`, `commission_amount`, `total`
- `notes`

### `order_items`
- `id` (PK)
- `order_id` (FK: `orders`)
- `vendor_inventory_id` (FK: `vendor_inventories`)
- `product_name_snapshot` (string)
- `unit_snapshot` (string)
- `unit_price_snapshot` (decimal)
- `quantity` (decimal)
- `subtotal` (decimal)
- `commission_amount` (decimal)
- `vendor_net_amount` (decimal)

---

## 6. Wallet Domain

### `wallets`
- `id` (PK)
- `user_id` (FK: `users`)
- `currency_id` (FK: `currencies`)
- `balance` (decimal, 16,4)

### `wallet_transactions`
- `id` (PK)
- `wallet_id` (FK: `wallets`)
- `wallet_transaction_type_id`
- `wallet_transaction_status_id`
- `amount` (decimal, 16,2)

---

## 7. System Tables

- `personal_access_tokens`: Sanctum API tokens.
- `migrations`: Track migration history.
- `jobs`, `failed_jobs`: Queue management.
