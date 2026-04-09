Database Schema (B2B)

This project has three user groups:
- Consumer: buys products.
- Operation: vendor/supplier account that provides products.
- Admin: system owner that controls catalog and operations.

Authorization rule (no direct FK between Users and Suppliers):
- Access is controlled by `users.user_type_id` and `users.user_status_id`.
- Operation API requires `UserTypes = Operation` and `UserStatuses = Active`.

Core user tables

-> Users
    id,
    first_name,
    last_name,
    email,
    image,
    phone_number,
    password,
    user_type_id,
    user_status_id,
    created_at,
    updated_at,
    deleted_at

-> UserStatuses
    id,
    name,
    created_at,
    updated_at

    + 1: Active
    + 2: Inactive
    + 3: Deleted

-> UserTypes
    id,
    name,
    created_at,
    updated_at

    + 1: Consumer
    + 2: Operation
    + 3: Admin

-> UserAddresses
    id,
    user_id,
    label,
    recipient_name,
    phone,
    address_line_1,
    address_line_2,
    city,
    province,
    postal_code,
    lat,
    long,
    address_map,
    created_at,
    updated_at,
    deleted_at

-> UserPaymentMethods
    id,
    user_id,
    payment_method_type_id,
    payment_method_status_id,
    label,
    card_holder_name,
    card_number,
    expiry_month,
    expiry_year,
    cvv,
    is_default,
    billing_address,
    billing_city,
    billing_state,
    billing_zip_code,
    created_at,
    updated_at,
    deleted_at

-> ConsumerProfiles (table: consumer_profiles)
    id,
    user_id,
    pin,
    date_of_birth,
    gender,
    preferred_language,
    preferences,
    created_at,
    updated_at

-> PaymentMethodStatuses
    id,
    name,
    created_at,
    updated_at

    + 1: Active
    + 2: Inactive
    + 3: Deleted

-> PaymentMethodTypes
    id,
    name,
    created_at,
    updated_at

    + 1: visa
    + 2: master_card
    + 3: union_pay
    + 4: american_express
    + 5: discover
    + 6: jcb
    + 7: diners_club
    + 8: paypal
    + 9: stripe

Catalog and inventory tables

-> ProductCategories (table: product_categories)
    id,
    name,
    slug,
    created_at,
    updated_at

-> ProductTypes
    id,
    code,
    name,
    created_at,
    updated_at

-> ProductStatuses
    id,
    code,
    name,
    created_at,
    updated_at

-> Units
    id,
    name,
    symbol,
    conversion_to_base,
    created_at,
    updated_at

    + kg: sold by weight (kilogram)
    + qty: sold by count (piece/bundle/pack quantity)

Cambodia Product Category Details

- Leafy Vegetables: morning glory, bok choy, chinese kale, mustard greens; mostly kg and some qty bundles.
- Fruiting Vegetables: tomato, cucumber, eggplant, bitter melon, pumpkin, bottle gourd; mixed kg and qty.
- Root Vegetables: carrot, white radish, sweet potato, cassava, turmeric, galangal; mostly kg.
- Herbs / Aromatic Plants: lemongrass, kaffir lime leaves, holy basil, mint, coriander, spring onion; mostly qty bundles.
- Legumes: long bean, yardlong bean, green bean, snow pea, soybean sprouts, fresh peanut pods; mostly kg with some qty packs.

-> Products
    id,
    product_category_id, (FK -> product_categories.id)
    product_type_id,
    default_unit_id,
    product_status_id,
    name,
    slug,
    description,
    nutrition_data,
    shelf_life_days,
    created_at,
    updated_at,
    deleted_at

-> ProductVariants
    id,
    product_id,
    unit_id,
    name,
    quantity_in_unit,
    price,
    created_at,
    updated_at

-> Suppliers
    id,
    name,
    contact_name,
    phone,
    email,
    address,
    created_at,
    updated_at

-> PurchaseOrders
    id,
    supplier_id,
    purchase_order_status_id,
    po_number,
    ordered_at,
    received_at,
    total_cost,
    created_at,
    updated_at

-> PurchaseOrderItems
    id,
    purchase_order_id,
    product_id,
    product_variant_id,
    qty_ordered,
    qty_received,
    cost_per_unit,
    expiry_date,
    batch_code,
    created_at,
    updated_at

-> InventoryBatches
    id,
    product_id,
    product_variant_id,
    supplier_id,
    inventory_batch_status_id,
    batch_code,
    received_qty,
    reserved_qty,
    sold_qty,
    damaged_qty,
    expired_qty,
    cost_per_unit,
    expiry_date,
    received_at,
    created_at,
    updated_at

-> InventoryMovements
    id,
    inventory_batch_id,
    inventory_movement_type_id,
    quantity,
    reference_type,
    reference_id,
    note,
    created_by,
    created_at,
    updated_at

-> PriceHistories
    id,
    product_id,
    product_variant_id,
    old_price,
    new_price,
    changed_by,
    changed_at,
    created_at,
    updated_at

Suggested initial data volume (recommended)

For Cambodia phase 1 MVP:
- ProductCategories: 5
- Products: 50-75
- ProductVariants: 120-180
- Operation vendors (UserTypes=2): 10-15
- Suppliers: 10-20
- InventoryBatches: 100+

How this works (simple flow)

1) Admin manages master catalog
   - Admin creates product categories, units, products, and variants.

2) Operation vendor supplies stock
   - Vendor data is represented in supplier and purchasing flow.
   - Purchase order and inventory batches are created when stock arrives.

3) Price and stock become sellable data
   - ProductVariants carry sale prices.
   - InventoryBatches and InventoryMovements track actual available stock.
   - PriceHistories keeps change history.

4) Consumer buys through API
   - Consumer sees available catalog, creates cart/order, and pays.
   - System reduces stock through inventory movement records.

Rule of thumb for product count

- If your API is still under active development, use 100 products / 250 variants.
- If you are validating real vendor behavior, move to 300-800 products quickly.
- If you are preparing for production launch, target 1,000+ products.
