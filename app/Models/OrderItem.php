<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
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
 * @property float $unit_price_snapshot
 * @property float $quantity
 * @property float $subtotal
 * @property float $commission_amount
 * @property float $vendor_net_amount
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
    'commission_amount',
    'vendor_net_amount',
])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(static function (OrderItem $item): void {
            $rate = Setting::get('commission_rate_percentage', 10.00);
            
            $item->commission_amount = $item->subtotal * ($rate / 100);
            $item->vendor_net_amount = $item->subtotal - $item->commission_amount;
        });
    }

    /**
     * Get the current commission percentage from settings.
     */
    public public(set) float $activeCommissionRate {
        get => (float) Setting::get('commission_rate_percentage', 10.00);
    }

    /**
     * Get the order that owns the item.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get the product for the item.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the product variant for the item.
     *
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }
}
