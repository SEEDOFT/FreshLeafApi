# Vendor Management

## Overview

Vendor registration, verification, and profile management system. Vendors are the sellers on the marketplace.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **Admin Vendor Controller** | `app/Http/Controllers/Api/Admin/Vendor/VendorController.php` | Approval management |
| **Vendor Profile Controller** | `app/Http/Controllers/Api/Vendor/ProfileController.php` | Profile management |
| **Vendor Address Controller** | `app/Http/Controllers/Api/Vendor/VendorAddressController.php` | Business addresses |
| **Vendor Auth Controller** | `app/Http/Controllers/Api/Vendor/AuthController.php` | Vendor authentication |
| **Vendor Profile Model** | `app/Models/VendorProfile.php` | Vendor entity |
| **Vendor Wallet Model** | `app/Models/Wallet.php` | Vendor wallet (via User) |

### Filament Resources

| Resource | Path | Description |
|-----------|------|-------------|
| VendorResource | `app/Filament/Resources/Vendors/VendorResource.php` | Vendor CRUD |
| VendorProfile | `app/Filament/Vendor/Clusters/Settings/Pages/VendorProfile.php` | Vendor settings |

## API Endpoints

### Vendor Registration & Auth

| Method | Endpoint | Description |
|--------|----------|--------------|
| POST | `/api/v1/vendor/auth/register` | Register new vendor |
| POST | `/api/v1/vendor/auth/login` | Vendor login |

### Vendor Profile

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/vendor/profile` | Get vendor profile |
| PATCH | `/api/v1/vendor/profile` | Update profile |

### Vendor Addresses

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/vendor/addresses` | List addresses |
| POST | `/api/v1/vendor/addresses` | Create address |
| PUT/PATCH/DELETE | `/api/v1/vendor/addresses/{id}` | Manage address |

### Vendor Products

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/vendor/products` | List vendor products |
| GET | `/api/v1/vendor/products/{id}` | Get product details |

### Admin Vendor Management

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/admin/vendors/pending` | List pending vendors |
| GET | `/api/v1/admin/vendors/pending/{id}` | View pending vendor |
| PATCH | `/api/v1/admin/vendors/pending/{id}` | Approve/reject vendor |

## Vendor Verification

Vendors go through an approval process:

1. **Registration** - Vendor registers via API or Filament
2. **Pending** - Awaiting admin verification
3. **Verification** - Admin reviews:
   - Business documents
   - ID card images
   - Bank details
4. **Approved/Rejected** - Account activated or rejected

### Vendor Profile Fields

- business_name
- shop_description
- is_verified (boolean)
- verified_at (timestamp)
- bank_name
- bank_account_number
- id_card_front
- id_card_back

## Database Tables

### vendor_profiles
Vendor business information:
- user_id (link to User)
- business_name
- shop_description
- is_verified
- verified_at
- bank details
- ID card images

### vendors (via User relationship)
- user_type_id = VENDOR (2)
- user_status_id (active/pending/inactive)

## Admin Panel

Admins can:
- View all vendors
- Approve/reject pending vendors
- View vendor details
- Manage vendor payouts

## Related Files

- `app/Filament/Resources/Vendors/` - Vendor management
- `app/Filament/Vendor/` - Vendor panel configuration
- `app/Models/User.php` - User with vendor profile relationship