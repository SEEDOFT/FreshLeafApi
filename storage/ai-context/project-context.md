# FreshLeaf Project Context

## Company Overview

FreshLeaf is a fresh produce delivery startup based in Cambodia, providing convenient online shopping for fresh vegetables, fruits, herbs, and other perishable goods.

## Service Area

- **Primary Focus**: **Phnom Penh** (Current priority for the startup phase).
- **Secondary Coverage**: Surrounding provinces in Cambodia (expanding soon).
- **Delivery Service**: Door-to-door delivery.

## Core Features

### 1. Digital Wallet System
- Users have an internal wallet for faster payments.
- Supports multi-currency tracking (primarily USD and KHR).
- Users can top-up, view full transaction history, and receive refunds directly to their wallet.

### 2. Product Catalog
- Fresh vegetables (Leafy, Fruiting, Root, Legumes).
- Fresh fruits and herbs.
- Snapshot-based pricing to ensure order accuracy.

### 3. Payment Methods
- **Internal Wallet**: Fastest payment method.
- **Online Payment**: Support for Credit/Debit cards, ABA, and ACLEDA bank transfers.
- **Local Options**: Support for ABA and ACLEDA bank transfers.
- **COD**: Cash on delivery support.

### 4. Real-Time AI Chat
- Powered by advanced LLMs (Gemini, Zen, or native Offline Llama.cpp).
- Features real-time token streaming for a natural conversation experience.

## Logistics & Delivery

- **Third-Party Fulfillment**: Delivery is handled by third-party delivery services.
- **No Real-Time Tracking**: There is currently no live tracking system for deliveries.
- **Confirmation Process**: Once an order is placed, the system or support team will confirm the delivery details back to the user. Users should expect a confirmation message or call regarding their delivery time.

## Operating Hours

- Online ordering: 24/7
- Customer support: Available during business hours

## Technical Infrastructure
- API-first architecture (Laravel 13).
- Real-time event broadcasting via Laravel Reverb WebSockets.
- Background job processing for high-performance AI generation.
