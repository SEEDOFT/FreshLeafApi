# Project Documentation - FreshLeaf API

## Overview
This documentation outlines the initial database schema implementation for the FreshLeaf API, covering eight core domains. All models are built using PHP 8.3 features, including Attributes for property definitions (`Fillable`, `Hidden`) and explicit return types.

## 1. Domains Implemented

### 1.1 User Domain
- **User:** Updated with `phone`, `user_type_id`, `status_id`, and Soft Deletes.
- **UserType:** Lookup for user roles/types.
- **UserStatus:** Lookup for account states.
- **Address:** Multi-address support for users.

### 1.2 Product Domain
- **Category:** Hierarchical organization (slug-based).
- **ProductType:** Classification of products.
- **ProductStatus:** Availability states.
- **Unit:** Measurement units with conversion factors.
- **Product:** Core product data with Soft Deletes.
- **ProductVariant:** Specific SKU data (price, quantity).
- **ProductSubstitution:** AI-driven or manual substitution mapping.

### 1.3 Supplier & Procurement Domain
- **Supplier:** Vendor contact information.
- **PurchaseOrderStatus:** PO lifecycle states.
- **PurchaseOrder:** Procurement records.
- **PurchaseOrderItem:** Line items for procurement.

### 1.4 Inventory Domain
- **InventoryBatchStatus:** State of specific batches (e.g., Available, Quarantined).
- **InventoryBatch:** Batch-level tracking (expiry, cost, received qty).
- **InventoryMovementType:** Types of stock changes (e.g., Sale, Damage, Adjustment).
- **InventoryMovement:** Audit trail of all stock changes.
- **PriceHistory:** Historical tracking of variant price changes.

### 1.5 Cart Domain
- **CartStatus:** Cart states (e.g., Active, Abandoned, Converted).
- **Cart:** User shopping sessions.
- **CartItem:** Products and variants currently in cart.

### 1.6 Order Domain
- **OrderType:** Types of orders (e.g., Delivery, Pickup).
- **OrderStatus:** Full fulfillment lifecycle states.
- **PaymentStatus & PaymentType:** Financial tracking.
- **Order:** Core transaction data.
- **OrderItem:** Snapshot-based order lines (capturing price/name at time of order).
- **Payment:** Individual payment transactions.
- **OrderStatusHistory:** Audit trail of order state changes.

### 1.7 AI Domain
- **BehaviorEventType:** Types of user actions tracked (e.g., View, Search).
- **UserBehaviorEvent:** Raw event log for AI processing.
- **AiRecommendationType & Status:** Recommendation metadata.
- **AiRecommendation:** Generated suggestions for users.
- **AiRecommendationItem:** Specific items within a recommendation.

### 1.8 Notification Domain
- **NotificationType:** Categories (e.g., Order, Promo).
- **NotificationStatus:** Read/Unread states.
- **Notification:** User alerts and messages.

### 1.9 Authentication & User CRUD (API)
- **Sanctum Integration:** Configured for secure API token-based authentication.
- **Endpoints:**
    - `POST /api/register`: Register with `first_name`, `last_name`, `phone_number`, `password`.
    - `POST /api/login`: Login with `phone_number` and `password`.
    - `POST /api/logout`: Revoke all tokens (Protected by Sanctum).
    - `GET /api/user`: Get current authenticated user (Protected by Sanctum).
- **Validation:** Implemented via dedicated Form Requests (`RegisterRequest`, `LoginRequest`).
- **User Table Updates:**
    - Split `name` into `first_name` and `last_name`.
    - Renamed `phone` to `phone_number`.
    - Renamed `status_id` to `user_status_id` to emphasize domain belonging.
    - Made `email` nullable to support phone-only registration.

### 1.10 API Documentation (Swagger)
- **Tool:** L5-Swagger (OpenAPI 3.0).
- **Access URL:** `/api/documentation` (e.g., `http://localhost:8000/api/documentation`).
- **Features:**
    - Interactive testing of all endpoints.
    - Token-based authentication support (Authorize button).
    - Clear visibility of request/response schemas.
- **Maintenance:** Run `php artisan l5-swagger:generate` to update documentation after annotation/attribute changes.

---

## 2. Technical Standards Applied
- **Models:** Used PHP 8.3 Attributes (`#[Fillable]`, `#[Hidden]`).
- **Migrations:** Used `constrained()` for foreign keys and `restrictOnDelete()` / `cascadeOnDelete()` where appropriate.
- **Soft Deletes:** Applied to `User` and `Product` models.
- **Strict Typing:** All relationships include return type hints.
- **Formatting:** Code formatted via Laravel Pint.

---

## 3. Roadmap (Next Steps)

### Phase 1: Database Initialization
1.  **Run Migrations:** `php artisan migrate` to build the SQLite/MySQL schema.
2.  **Seed Lookups:** Implement seeders for all `_types` and `_statuses` tables.
3.  **Verify Factories:** Complete `faker` definitions in generated factories.

### Phase 2: Core API Development
1.  **Auth System:** Implement Laravel Sanctum/Fortify for user authentication.
2.  **Product Catalog:** Build READ/SEARCH endpoints for Products and Categories.
3.  **Inventory Management:** Implement logic for receiving POs and updating `InventoryBatches`.

### Phase 3: Transactional Logic
1.  **Cart & Checkout:** Build the cart management and order placement flow.
2.  **Inventory Deductions:** Implement listeners to move inventory when orders are placed.
3.  **Notifications:** Hook up notifications to order status changes.

### Phase 4: AI & Analytics
1.  **Event Tracking:** Middleware/Events to log `UserBehaviorEvents`.
2.  **Recommendation Engine:** Integration points for AI-generated payloads.
