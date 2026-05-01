# Admin Panel (Filament)

## Overview

Filament-based admin panel for managing all aspects of the FreshLeaf marketplace. Separate panels for Admin and Vendor roles.

## Panels

### Admin Panel
- URL: `/admin`
- Manages: Users, Vendors, Products, Orders, Wallets, Settings

### Vendor Panel
- URL: `/vendor`
- Manages: Own products, orders, payouts, profile

## Components

### Clusters (Navigation Groups)

| Cluster | Description |
|---------|-------------|
| `Filament/Clusters/Settings.php` | Admin settings |
| `Filament/Vendor/Clusters/Settings.php` | Vendor settings |

### Admin Resources

| Resource | Path | Description |
|----------|------|-------------|
| UserResource | `Filament/Resources/Users/UserResource.php` | User management |
| ProductResource | `Filament/Resources/Products/ProductResource.php` | Product management |
| OrderResource | `Filament/Resources/Orders/OrderResource.php` | Order management |
| VendorResource | `Filament/Resources/Vendors/VendorResource.php` | Vendor management |
| WalletResource | `Filament/Resources/Wallets/WalletResource.php` | Wallet management |
| PayoutResource | `Filament/Resources/Payouts/PayoutResource.php` | Payout management |

### Admin Widgets

| Widget | Description |
|--------|-------------|
| AdminStatsOverview | Dashboard statistics |
| AdminRevenueChart | Revenue chart |
| AdminCommissionWidget | Commission tracking |

### Vendor Resources

| Resource | Description |
|----------|-------------|
| Products | Manage own products |
| Orders | View orders for vendor's products |
| Payouts | Request payouts |

### Vendor Widgets

| Widget | Description |
|--------|-------------|
| VendorStatsOverview | Sales statistics |
| VendorEarningsChart | Earnings visualization |

### Admin Pages

| Page | Path | Description |
|------|------|-------------|
| SupportChat | `Filament/Pages/SupportChat.php` | Customer support |
| AiAssistant | `Filament/Pages/AiAssistant.php` | AI assistant |

## Authentication

Admin/Vendor login via Filament:
- `Filament/Pages/Auth/Login.php`
- `Filament/Pages/Auth/Register.php`

Uses Laravel's built-in auth (session-based).

## Settings

### Admin Settings
- Profile management
- Application settings

### Vendor Settings
- Business profile
- Financial details
- Security (password)
- Verification documents

## Customization

### Theme
- `Filament/ThemeColors.php` - Custom theme colors

### Localization
- Khmer (km) and English (en) support
- Per-profile language preference

## Related Files

- `config/filament.php` - Filament configuration
- `app/Providers/Filament/AdminPanelProvider.php` - Panel setup
- `app/Filament/Clusters/` - Navigation clusters
- `lang/en/`, `lang/km/` - Translation files