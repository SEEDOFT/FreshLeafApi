# Project Documentation - FreshLeaf API

## Overview
This documentation outlines the implementation status of the FreshLeaf API, covering database schema, API endpoints, payment integrations, and the roadmap for future development. All models are built using PHP 8.3 features, including Attributes for property definitions (`Fillable`, `Hidden`) and explicit return types.

---

## ✅ Completed Work

### 1. Payment Method Management
- **PaymentMethod Model:** Full CRUD with encryption for sensitive fields (`card_holder_name`, `card_number`, `cvv`). Billing address fields added (`billing_address`, `billing_city`, `billing_state`, `billing_zip_code`).
- **PaymentMethodType:** 9 payment types seeded (Visa, Mastercard, UnionPay, American Express, Discover, JCB, Diners Club, PayPal, Stripe).
- **PaymentMethodStatus:** 3 statuses seeded (Active, Inactive, Deleted).
- **PaymentMethodController:** Full REST API with `index`, `store`, `show`, `update`, `replace`, `destroy`. Soft-delete via status flag.
- **Form Requests:** `StorePaymentMethodRequest`, `UpdatePaymentMethodRequest`, `ReplacePaymentMethodRequest` with validation and is_default uniqueness checks.
- **PaymentMethodResource:** Transforms output with masked card numbers.

### 2. Stripe Sandbox Integration
- **Package:** `stripe/stripe-php` v20.0.0 installed.
- **StripeService:** Full service class with methods for:
  - `createCustomer()` / `getCustomer()`
  - `attachPaymentMethod()` / `detachPaymentMethod()` / `listPaymentMethods()`
  - `createPaymentIntent()` / `getPaymentIntent()` / `confirmPaymentIntent()` / `cancelPaymentIntent()`
  - `createRefund()`
  - `verifyWebhookSignature()`
  - `setDefaultPaymentMethod()`
- **PaymentController Endpoints:**
  - `POST /api/v1/users/payments/intent` — Create Stripe payment intent
  - `POST /api/v1/users/payments/confirm` — Confirm Stripe payment
  - `POST /api/v1/users/payments/refund` — Process Stripe refund
  - `GET /api/v1/users/payments/status` — Check Stripe payment status
  - `POST /api/v1/webhooks/stripe` — Handle Stripe webhooks (no auth)
- **Webhook Handlers:** `payment_intent.succeeded`, `payment_intent.payment_failed`, `payment_intent.canceled`, `charge.refunded`.
- **Configuration:** `.env` keys (`STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`) and `config/services.php` stripe section.

### 3. PayPal Sandbox Integration
- **Package:** `paypal/paypal-server-sdk` v2.2.0 installed (official PayPal SDK).
- **PayPalService:** Full service class with methods for:
  - `createOrder()` — Create PayPal checkout order
  - `getOrder()` — Retrieve order details
  - `captureOrder()` — Capture/complete payment
  - `authorizeOrder()` — Authorize payment
  - `refundCapture()` — Process refunds
  - `getRefund()` — Get refund details
  - `createPaymentToken()` — Vault for recurring payments
  - `verifyWebhook()` — Verify webhook signatures
- **PaymentController Endpoints:**
  - `POST /api/v1/users/payments/paypal/order` — Create PayPal order
  - `POST /api/v1/users/payments/paypal/capture` — Capture PayPal payment
  - `GET /api/v1/users/payments/paypal/status` — Check PayPal order status
  - `POST /api/v1/users/payments/paypal/refund` — Process PayPal refund
  - `POST /api/v1/webhooks/paypal` — Handle PayPal webhooks (no auth)
- **Webhook Handlers:** `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.DENIED`, `PAYMENT.CAPTURE.REFUNDED`, `CHECKOUT.ORDER.APPROVED`.
- **Form Requests:** `CreatePayPalOrderRequest`, `CapturePayPalOrderRequest`.
- **Configuration:** `.env` keys (`PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, `PAYPAL_MODE`, `PAYPAL_WEBHOOK_ID`) and `config/services.php` paypal section.

### 4. Database Seeders
- **PaymentMethodTypeSeeder:** Seeds all 9 payment method types using model constants.
- **PaymentMethodStatusSeeder:** Seeds all 3 payment method statuses using model constants.
- **DatabaseSeeder:** Updated to call both seeders.

### 5. Migrations
- `2026_04_04_025456_add_additional_payment_method_types` — Seeds Amex, Discover, JCB, Diners Club, PayPal, Stripe types.

---

## 📋 Existing Domains (Previously Implemented)

### User Domain
- **User:** Updated with `phone`, `user_type_id`, `status_id`, and Soft Deletes.
- **UserType:** Lookup for user roles/types.
- **UserStatus:** Lookup for account states.
- **Address:** Multi-address support for users.

### Product Domain
- **Category:** Hierarchical organization (slug-based).
- **ProductType:** Classification of products.
- **ProductStatus:** Availability states.
- **Unit:** Measurement units with conversion factors.
- **Product:** Core product data with Soft Deletes.
- **ProductVariant:** Specific SKU data (price, quantity).
- **ProductSubstitution:** AI-driven or manual substitution mapping.

### Supplier & Procurement Domain
- **Supplier:** Vendor contact information.
- **PurchaseOrderStatus:** PO lifecycle states.
- **PurchaseOrder:** Procurement records.
- **PurchaseOrderItem:** Line items for procurement.

### Inventory Domain
- **InventoryBatchStatus:** State of specific batches (e.g., Available, Quarantined).
- **InventoryBatch:** Batch-level tracking (expiry, cost, received qty).
- **InventoryMovementType:** Types of stock changes (e.g., Sale, Damage, Adjustment).
- **InventoryMovement:** Audit trail of all stock changes.
- **PriceHistory:** Historical tracking of variant price changes.

### Cart Domain
- **CartStatus:** Cart states (e.g., Active, Abandoned, Converted).
- **Cart:** User shopping sessions.
- **CartItem:** Products and variants currently in cart.

### Order Domain
- **OrderType:** Types of orders (e.g., Delivery, Pickup).
- **OrderStatus:** Full fulfillment lifecycle states.
- **PaymentStatus & PaymentType:** Financial tracking.
- **Order:** Core transaction data.
- **OrderItem:** Snapshot-based order lines (capturing price/name at time of order).
- **Payment:** Individual payment transactions.
- **OrderStatusHistory:** Audit trail of order state changes.

### AI Domain
- **BehaviorEventType:** Types of user actions tracked (e.g., View, Search).
- **UserBehaviorEvent:** Raw event log for AI processing.
- **AiRecommendationType & Status:** Recommendation metadata.
- **AiRecommendation:** Generated suggestions for users.
- **AiRecommendationItem:** Specific items within a recommendation.

### Notification Domain
- **NotificationType:** Categories (e.g., Order, Promo).
- **NotificationStatus:** Read/Unread states.
- **Notification:** User alerts and messages.

---

## 🔧 Technical Standards Applied
- **Models:** Used PHP 8.3 Attributes (`#[Fillable]`, `#[Hidden]`).
- **Migrations:** Used `constrained()` for foreign keys and `restrictOnDelete()` / `cascadeOnDelete()` where appropriate.
- **Soft Deletes:** Applied to `User` and `Product` models.
- **Strict Typing:** All relationships include return type hints.
- **Formatting:** Code formatted via Laravel Pint.
- **Payment Encryption:** Sensitive card data encrypted at rest using Laravel's `encrypted` cast.
- **Service Layer:** Payment processing abstracted into dedicated service classes (`StripeService`, `PayPalService`).
- **Webhook Security:** Both Stripe and PayPal webhooks verify signatures before processing.

---

## 🚀 Roadmap (Next Steps)

### Phase 1: Payment Activation
1. **Configure Stripe Keys:** Add real sandbox credentials to `.env` (`STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`).
2. **Configure PayPal Keys:** Add real sandbox credentials to `.env` (`PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, `PAYPAL_WEBHOOK_ID`).
3. **Create PayPal Webhook:** Register webhook in PayPal Developer Dashboard with URL `https://your-domain.com/api/v1/webhooks/paypal`.
4. **Create Stripe Webhook:** Register webhook in Stripe Dashboard with URL `https://your-domain.com/api/v1/webhooks/stripe`.
5. **Test Payment Flows:** End-to-end testing of Stripe and PayPal sandbox payments.

### Phase 2: Checkout & Order Completion
1. **Checkout Flow:** Build checkout endpoint that ties Cart → Order → Payment together.
2. **Order Placement:** Create order from cart items, calculate totals, apply taxes/shipping.
3. **Inventory Deduction:** Deduct stock when order is placed/confirmed.
4. **Payment Linking:** Associate payments with orders via `order_id` foreign key.

### Phase 3: Advanced Payment Features
1. **Recurring Payments:** Implement PayPal vault + Stripe subscriptions for recurring billing.
2. **Partial Refunds:** Support partial refund amounts for both Stripe and PayPal.
3. **Payment History:** Build endpoint to list all payments for a user/order.
4. **Multi-Payment Support:** Allow split payments across multiple methods.

### Phase 4: Core API Development
1. **Product Catalog:** Build READ/SEARCH endpoints for Products and Categories.
2. **Inventory Management:** Implement logic for receiving POs and updating `InventoryBatches`.
3. **Cart Endpoints:** Full cart management API (already partially implemented).

### Phase 5: AI & Analytics
1. **Event Tracking:** Middleware/Events to log `UserBehaviorEvents`.
2. **Recommendation Engine:** Integration points for AI-generated payloads.
3. **Analytics Dashboard:** Sales, user behavior, and inventory analytics endpoints.

### Phase 6: Production Readiness
1. **Switch to Live Mode:** Update `.env` to production keys for Stripe and PayPal.
2. **Rate Limiting:** Add API rate limiting for payment endpoints.
3. **Idempotency Keys:** Prevent duplicate payment submissions.
4. **Comprehensive Testing:** Unit and feature tests for all payment flows.
5. **Monitoring & Logging:** Enhanced logging for payment failures and webhook errors.
