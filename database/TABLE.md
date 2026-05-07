Database Schema (B2B)

This project has three user groups:
- Consumer: buys products.
- Operation: vendor account that provides products.
- Admin: system owner that controls catalog and operations.

Authorization rule:
- Access is controlled by `users.user_type_id` and `users.user_status_id`.
- Operation API requires `UserTypes = Vendor` and `UserStatuses = Active`.

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

    + 1: Pending
    + 2: Active
    + 3: Inactive
    + 4: Deleted

-> UserTypes
    id,
    name,
    created_at,
    updated_at

    + 1: Admin
    + 2: Vendor
    + 3: Consumer

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

-> PaymentMethods
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

    + 1: wallet
    + 2: credit_debit
    + 3: aba
    + 4: acleda

Wallet and transaction tables

-> Wallets
    id,
    user_id,
    currency_id,
    balance,
    created_at,
    updated_at

-> WalletTransactionTypes
    id,
    code,
    name,
    created_at,
    updated_at

    + 1: top_up
    + 2: purchase
    + 3: refund
    + 4: withdrawal

-> WalletTransactionStatuses
    id,
    code,
    name,
    created_at,
    updated_at

    + 1: pending
    + 2: completed
    + 3: failed
    + 4: cancelled

-> WalletTransactions
    id,
    wallet_id,
    wallet_transaction_type_id,
    wallet_transaction_status_id,
    amount,
    reference_type,
    reference_id,
    description,
    created_at,
    updated_at

-> WalletTransactionHistories
    id,
    wallet_transaction_id,
    from_wallet_transaction_status_id,
    to_wallet_transaction_status_id,
    changed_by_user_id,
    note,
    created_at,
    updated_at

Catalog and inventory tables

-> ProductCategories (table: product_categories)
    id,
    product_category_status_id,
    name_en,
    name_km,
    slug,
    description_en,
    description_km,
    image_url,
    created_at,
    updated_at

-> ProductCategoryStatuses
    id,
    code,
    name

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
    product_category_id,
    product_type_id,
    default_unit_id,
    product_status_id,
    name_en,
    name_km,
    slug,
    description_en,
    description_km,
    nutrition_data,
    image_url,
    created_at,
    updated_at,
    deleted_at

-> VendorInventories
    id,
    vendor_id,
    product_id,
    inventory_status_id,
    price,
    stock_quantity,
    unit_id,
    harvest_date,
    farm_location,
    province_of_origin,
    certification_type,
    packaging_type,
    shelf_life_days,
    batch_images,
    created_at,
    updated_at,
    deleted_at

-> VendorInventoryStatuses
    id,
    code,
    name

Cart and Wishlist tables

-> UserCarts
    id,
    user_id,
    user_cart_status_id,
    user_cart_type_id

-> UserCartStatuses / UserCartTypes
    id, code, name

-> UserCartItems
    id,
    cart_id,
    vendor_inventory_id,
    user_cart_item_status_id,
    user_cart_item_type_id,
    quantity,
    unit_price,
    subtotal

-> UserCartItemStatuses / UserCartItemTypes
    id, code, name

-> UserWishlists
    id,
    user_id,
    user_wishlist_status_id,
    user_wishlist_type_id

-> UserWishlistStatuses / UserWishlistTypes
    id, code, name

-> UserWishlistItems
    id,
    user_wishlist_id,
    vendor_inventory_id,
    user_wishlist_item_status_id,
    user_wishlist_item_type_id

-> UserWishlistItemStatuses / UserWishlistItemTypes
    id, code, name

How this works (simple flow)

1) Admin manages master catalog (Dictionary)
   - Admin creates product categories, units, and base products.

2) Vendor supplies stock
   - Vendors add physical inventory (VendorInventories) pointing to master products.
   - Vendors define price, stock quantity, and physical origin attributes per batch.

3) Consumer buys through API
   - Consumer browses vendor inventory listings.
   - Consumer adds specific vendor inventory items to UserCarts or UserWishlists.
   - Checkout generates orders based on cart items.
