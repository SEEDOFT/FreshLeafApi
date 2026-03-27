# Database Schema Reference - FreshLeaf API

This document provides a detailed overview of the database schema for the FreshLeaf API. The schema is organized into eight core domains to ensure scalability and clarity.

## Table of Contents
1. [User Domain](#1-user-domain)
2. [Product Domain](#2-product-domain)
3. [Supplier & Procurement Domain](#3-supplier--procurement-domain)
4. [Inventory Domain](#4-inventory-domain)
5. [Cart Domain](#5-cart-domain)
6. [Order Domain](#6-order-domain)
7. [AI Domain](#7-ai-domain)
8. [Notification Domain](#8-notification-domain)
9. [System Tables](#9-system-tables)

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
- `code` (string, unique) - e.g., 'customer', 'admin', 'supplier'.
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

---

## 2. Product Domain

### `categories`
- `id` (PK)
- `name` (string)
- `slug` (string, unique, index)

### `products`
- `id` (PK)
- `category_id` (FK: `categories`)
- `product_type_id` (FK: `product_types`)
- `default_unit_id` (FK: `units`)
- `product_status_id` (FK: `product_statuses`)
- `name` (string)
- `slug` (string, unique, index)
- `description` (text, nullable)
- `nutrition_data` (json, nullable)
- `shelf_life_days` (integer, nullable)
- `deleted_at` (timestamp, soft delete)

### `product_variants`
- `id` (PK)
- `product_id` (FK: `products`, cascade delete)
- `unit_id` (FK: `units`)
- `name` (string) - e.g., '500g Pack', 'Bulk 5kg'.
- `quantity_in_unit` (decimal)
- `price` (decimal, 12,2)

### `units`
- `id` (PK)
- `name` (string)
- `symbol` (string) - e.g., 'kg', 'g', 'pcs'.
- `conversion_to_base` (decimal)

---

## 3. Supplier & Procurement Domain

### `suppliers`
- `id` (PK)
- `name` (string)
- `contact_name` (string, nullable)
- `phone`, `email` (strings, nullable)
- `address` (text, nullable)

### `purchase_orders`
- `id` (PK)
- `supplier_id` (FK: `suppliers`)
- `purchase_order_status_id` (FK: `purchase_order_statuses`)
- `po_number` (string, unique, index)
- `ordered_at`, `received_at` (timestamps)
- `total_cost` (decimal)

---

## 4. Inventory Domain

### `inventory_batches`
- `id` (PK)
- `product_id` (FK: `products`)
- `product_variant_id` (FK: `product_variants`)
- `batch_code` (string, unique, index)
- `received_qty`, `reserved_qty`, `sold_qty`, `damaged_qty`, `expired_qty` (decimals)
- `expiry_date` (date, nullable)
- `received_at` (timestamp)

### `inventory_movements`
- `id` (PK)
- `inventory_batch_id` (FK: `inventory_batches`)
- `inventory_movement_type_id` (FK: `inventory_movement_types`)
- `quantity` (decimal)
- `reference_type`, `reference_id` (strings, nullable) - Morph link to orders/POs.
- `created_by` (FK: `users`)

---

## 5. Cart Domain

### `carts`
- `id` (PK)
- `user_id` (FK: `users`, cascade delete)
- `cart_status_id` (FK: `cart_statuses`)

### `cart_items`
- `id` (PK)
- `cart_id` (FK: `carts`, cascade delete)
- `product_id` (FK: `products`)
- `product_variant_id` (FK: `product_variants`)
- `quantity` (decimal)
- `unit_price`, `subtotal` (decimals)

---

## 6. Order Domain

### `orders`
- `id` (PK)
- `user_id` (FK: `users`)
- `address_id` (FK: `addresses`)
- `order_number` (string, unique, index)
- `total_amount` (decimal)
- `order_status_id` (FK: `order_statuses`)
- `payment_status_id` (FK: `payment_statuses`)

---

## 7. AI Domain

### `user_behavior_events`
- `id` (PK)
- `user_id` (FK: `users`, set null on delete)
- `behavior_event_type_id` (FK: `behavior_event_types`)
- `product_id`, `product_variant_id` (FKs: nullable)
- `metadata` (json, nullable)

### `ai_recommendations`
- `id` (PK)
- `user_id` (FK: `users`, cascade delete)
- `ai_recommendation_type_id` (FK: `ai_recommendation_types`)
- `score` (decimal, 8,4)

---

## 8. Notification Domain

### `notifications`
- `id` (PK)
- `user_id` (FK: `users`, cascade delete)
- `notification_type_id` (FK: `notification_type`)
- `title`, `message` (strings/text)
- `data` (json, nullable)
- `read_at` (timestamp, nullable)

---

## 9. System Tables

- `personal_access_tokens`: Sanctum API tokens.
- `migrations`: Track migration history.
- `password_reset_tokens`: Core auth tokens.
- `sessions`: Persistent PHP sessions.
- `jobs`, `job_batches`, `failed_jobs`: Queue management.
- `cache`, `cache_locks`: Application caching.
