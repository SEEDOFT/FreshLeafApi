<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $unit_id
 * @property string $name
 * @property numeric $quantity_in_unit
 * @property numeric $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 * @property-read Unit $unit
 * @property-read Collection<int, PriceHistory> $priceHistories
 * @property-read Collection<int, InventoryBatch> $inventoryBatches
 * @property-read Collection<int, InventoryMovement> $inventoryMovements
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read Collection<int, CartItem> $cartItems
 * @property-read Collection<int, PurchaseOrderItem> $purchaseOrderItems
 * @property-read int|null $ai_recommendation_items_count
 * @property-read int|null $cart_items_count
 * @property-read int|null $inventory_batches_count
 * @property-read int|null $inventory_movements_count
 * @property-read int|null $order_items_count
 * @property-read int|null $price_histories_count
 * @property-read int|null $purchase_order_items_count
 * @property-read int|null $user_behavior_events_count
 */
#[Table('product_variants', key: 'id')]
#[Fillable([
    'product_id',
    'unit_id',
    'name',
    'quantity_in_unit',
    'price',
])]
#[UseFactory(ProductVariantFactory::class)]
class ProductVariant extends Model
{
    use HasFactory;

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit for the variant.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the price histories for the product variant.
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Get the inventory batches for the product variant.
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    /**
     * Get the inventory movements for the product variant.
     */
    public function inventoryMovements(): HasManyThrough
    {
        return $this->hasManyThrough(InventoryMovement::class, InventoryBatch::class);
    }

    /**
     * Get the order items for the product variant.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the cart items for the product variant.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the purchase order items for the product variant.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
