<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int $product_variant_id
 * @property numeric $quantity
 * @property numeric $unit_price
 * @property numeric $subtotal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Cart $cart
 * @property-read Product|null $product
 * @property-read ProductVariant $variant
 */
#[Table('cart_items', key: 'id')]
#[Fillable([
    'cart_id',
    'product_id',
    'product_variant_id',
    'quantity',
    'unit_price',
    'subtotal',
])]
#[UseFactory(CartItemFactory::class)]
class CartItem extends Model
{
    use HasFactory;

    /**
     * Get the cart that owns the item.
     */
    /**
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    /**
     * Get the product for the item.
     */
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the product variant for the item.
     */
    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }
}
