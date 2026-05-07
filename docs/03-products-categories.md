# Products & Categories

## Overview

Product catalog system using a "Dictionary" and "Inventory" architecture. The master products act as a dictionary (defined by Admins), while physical stock and pricing are managed as Vendor Inventory.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **Category Controller** | `app/Http/Controllers/Api/Product/CategoryController.php` | Category listing |
| **Product Controller** | `app/Http/Controllers/Api/Product/ProductController.php` | Vendor Inventory listing |
| **Product Model** | `app/Models/Product.php` | Master Product Dictionary |
| **Product Category Model** | `app/Models/ProductCategory.php` | Category entity |
| **Vendor Inventory Model** | `app/Models/VendorInventory.php` | Physical stock and pricing |

## API Endpoints

### Categories

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/categories` | List all active categories |
| GET | `/api/v1/categories/{slug}` | Get category by slug |

### Products (Consumer App)

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/products` | List active vendor inventories (products available for sale) |
| GET | `/api/v1/products/{id}` | Get specific vendor inventory details |

### Admin Management

| Method | Endpoint | Description |
|--------|----------|--------------|
| Filament | `app/Filament/Admin/Resources/Products/` | Manage master product dictionary |
| Filament | `app/Filament/Admin/Resources/VendorInventoryResource.php` | Global view of all vendor listings |

### Vendor Management

| Method | Endpoint | Description |
|--------|----------|--------------|
| Filament | `app/Filament/Vendor/Resources/Products/` | Manage own physical stock (`VendorInventory`) |

## Database Architecture

### product_categories
Categories for products (Leafy Vegetables, Fruiting Vegetables, etc.)
- Bilingual support: name_en, name_km
- Slug for URL-friendly URLs
- Status: `product_category_status_id`

### products (Dictionary)
Master product table defined by Admins. Contains no pricing or physical stock info.
- Bilingual fields: name_en, name_km, description_en, description_km
- nutrition_data (JSON)
- image_url
- Status: `product_status_id`

### vendor_inventories (Listings)
Physical stock items offered by vendors.
- vendor_id
- product_id (Link to dictionary)
- price
- stock_quantity
- unit_id
- Physical details: province_of_origin, certification_type, farm_location, packaging_type, shelf_life_days
- Status: `inventory_status_id`

### units
Units of measurement (kg, g, pcs) with conversion factors.

## Organic Product Tracking

Organic tracking is now handled at the batch level in `vendor_inventories` via:
- farm_location
- certification_type
- harvest_date