<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $product_variant_id
 * @property string $product_name_snapshot
 * @property string $unit_snapshot
 * @property numeric $unit_price_snapshot
 * @property numeric $quantity
 * @property numeric $subtotal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read Product|null $product
 * @property-read ProductVariant $variant
 */
#[Table('order_items', key: 'id')]
#[Fillable([
    'order_id',
    'product_id',
    'product_variant_id',
    'product_name_snapshot',
    'unit_snapshot',
    'unit_price_snapshot',
    'quantity',
    'subtotal',
])]
#[UseFactory(OrderItemFactory::class)]
class OrderItem extends Model
{
    use HasFactory;

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant for the item.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
