<?php

declare(strict_types=1);

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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read Collection<int, CartItem> $cartItems
 * @property-read int|null $ai_recommendation_items_count
 * @property-read int|null $cart_items_count
 * @property-read int|null $order_items_count
 * @property-read int|null $price_histories_count
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
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(static function (ProductVariant $variant): void {
            if ($variant->wasChanged('price')) {
                PriceHistory::create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'old_price' => $variant->getOriginal('price'),
                    'new_price' => $variant->price,
                    'changed_by' => Auth::id() ?? 1,
                    'changed_at' => now(),
                ]);
            }
        });
    }

    /**
     * Get the product that owns the variant.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the unit for the variant.
     *
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    /**
     * Get the current active price in USD after applying any product-level discounts.
     */
    public public(set) float $activePrice {
        get {
            $basePrice = (float) $this->price;
            $discount = $this->product?->discountPercentage ?? 0;

            if ($discount <= 0) {
                return $basePrice;
            }

            return $basePrice * (1 - ($discount / 100));
        }
    }

    /**
     * Get the current active price in KHR.
     */
    public public(set) float $activePriceKhr {
        get => $this->activePrice |> (fn($price) => $price * ExchangeRate::getRate('USD', 'KHR'));
    }

    /**
     * Get the price history for the product variant.
     *
     * @return HasMany<PriceHistory, $this>
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'product_variant_id', 'id');
    }

    /**
     * Get the order items for the product variant.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id', 'id');
    }

    /**
     * Get the cart items for the product variant.
     *
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_variant_id', 'id');
    }
}
