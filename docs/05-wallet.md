# Wallet System

## Overview

Digital wallet system supporting dual currency (KHR and USD) with full transaction history and balance tracking.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **User Wallet Controller** | `app/Http/Controllers/Api/User/WalletController.php` | User wallet access |
| **Wallet Transaction Controller** | `app/Http/Controllers/Api/User/WalletTransactionController.php` | Transaction CRUD |
| **Admin Wallet Controller** | `app/Http/Controllers/Api/Admin/WalletController.php` | Admin wallet view |
| **Vendor Wallet Controller** | `app/Http/Controllers/Api/Vendor/WalletController.php` | Vendor wallet access |
| **Wallet Model** | `app/Models/Wallet.php` | Wallet entity |
| **Wallet Transaction Model** | `app/Models/WalletTransaction.php` | Transaction entity |
| **Wallet History Model** | `app/Models/WalletHistory.php` | Balance change history |

## API Endpoints

### User Wallets

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/user/wallets` | List user's wallets |
| GET | `/api/v1/user/wallets/{id}` | Get wallet details |
| GET | `/api/v1/user/wallets/{id}/histories` | Get balance history |

### Wallet Transactions

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/user/wallet-transactions` | List transactions |
| GET | `/api/v1/user/wallet-transactions/{id}` | Get transaction |
| POST | `/api/v1/user/wallet-transactions` | Create transaction |
| PATCH | `/api/v1/user/wallet-transactions/{id}` | Update transaction |
| DELETE | `/api/v1/user/wallet-transactions/{id}` | Delete transaction |

### Admin Wallets

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/admin/wallets` | List all wallets |
| GET | `/api/v1/admin/wallets/{id}` | Get wallet details |
| GET | `/api/v1/admin/wallets/{id}/histories` | Get history |

## Database Tables

### wallets
User wallets:
- user_id (owner)
- currency_id (KHR or USD)
- balance
- is_active

### wallet_transactions
Individual transactions:
- wallet_id
- transaction_type_id (top_up, purchase, refund, withdrawal)
- amount
- reference_id, reference_type (for linking to orders)
- status (pending, completed, failed)

### wallet_histories
Balance change audit trail:
- wallet_id
- amount_before, amount_after
- change_amount
- description

### wallet_transaction_types
Transaction type lookup:
- top_up (add funds)
- purchase (pay for order)
- refund (receive refund)
- withdrawal (withdraw funds)

### wallet_transaction_statuses
Transaction status lookup

## Currencies

| Currency | Code |
|----------|------|
| US Dollar | USD |
| Cambodian Riel | KHR |

## Transaction Flow

```
Top Up:
1. User initiates top-up
2. Transaction created with PENDING status
3. Payment confirmed → COMPLETED
4. Wallet balance updated

Purchase:
1. User places order
2. Transaction created with PENDING status
3. Order completed → COMPLETED
4. Wallet balance deducted

Refund:
1. Order cancelled/refunded
2. Refund transaction created
3. Wallet balance credited
```

## Related Files

- `app/Filament/Resources/Wallets/` - Admin wallet management
- `app/Filament/Resources/WalletTransactions/` - Transaction management