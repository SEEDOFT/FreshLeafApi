# Products & Categories

## Overview

Product catalog system with categories, variants, discounts, and organic product tracking.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **Category Controller** | `app/Http/Controllers/Api/CategoryController.php` | Category listing |
| **Product Controller** | `app/Http/Controllers/Api/Product/ProductController.php` | Product CRUD |
| **Product Model** | `app/Models/Product.php` | Product entity |
| **Product Category Model** | `app/Models/ProductCategory.php` | Category entity |
| **Product Variant Model** | `app/Models/ProductVariant.php` | Product variants |
| **Product Discount Model** | `app/Models/ProductDiscount.php` | Active discounts |

## API Endpoints

### Categories

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/categories` | List all categories |
| GET | `/api/v1/categories/{slug}` | Get category by slug |

### Products (User - Consumer)

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/user/products` | List products for consumers |
| GET | `/api/v1/user/products/{id}` | Get product details |

### Products (Admin)

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/admin/products` | List all products (admin view) |
| GET | `/api/v1/admin/products/{id}` | Get product (admin details) |

### Products (Vendor)

| Method | Endpoint | Description |
|--------|----------|--------------|
| GET | `/api/v1/vendor/products` | List vendor's products |
| GET | `/api/v1/vendor/products/{id}` | Get vendor product details |

## Database Tables

### product_categories
Categories for products (Leafy Vegetables, Fruiting Vegetables, etc.)
- Bilingual support: name_en, name_km
- Slug for URL-friendly URLs

### products
Main product table:
- Bilingual fields: name_en, name_km
- is_organic flag
- user_id (vendor owner)
- Pricing: price_khr, price_usd
- Stock management

### product_variants
Product variants (e.g., "500g Pack", "Bulk 5kg")
- Different prices per variant

### product_discounts
Active discounts with:
- percentage (discount amount)
- start_date, end_date

### units
Units of measurement (kg, g, pcs) with conversion factors

## Organic Product Tracking

Products include organic traceability:
- farm_location
- farming_method
- harvest_date

## Related Files

- `app/Filament/Resources/Products/` - Admin product management
- `app/Filament/Vendor/Resources/Products/` - Vendor product management