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
 * @property int $vendor_inventory_id
 * @property int $user_cart_item_status_id
 * @property int $user_cart_item_type_id
 * @property float $quantity
 * @property float $unit_price
 * @property float $subtotal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Cart $cart
 * @property-read VendorInventory $vendorInventory
 * @property-read UserCartItemStatus $status
 * @property-read UserCartItemType $type
 */
#[Table('user_cart_items', key: 'id')]
#[Fillable([
    'cart_id',
    'vendor_inventory_id',
    'user_cart_item_status_id',
    'user_cart_item_type_id',
    'quantity',
    'unit_price',
    'subtotal',
])]
#[UseFactory(CartItemFactory::class)]
class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Get the cart that owns the cart item.
     *
     * @return BelongsTo<Cart, $this>
     */
    /**
     * Get the cart that owns the cart item.
     *
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    /**
     * Get the vendor inventory that owns the cart item.
     *
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(VendorInventory::class, 'vendor_inventory_id', 'id');
    }

    /**
     * Get the status that owns the cart item.
     *
     * @return BelongsTo<UserCartItemStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(UserCartItemStatus::class, 'user_cart_item_status_id');
    }

    /**
     * Get the type that owns the cart item.
     *
     * @return BelongsTo<UserCartItemType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(UserCartItemType::class, 'user_cart_item_type_id');
    }
}
