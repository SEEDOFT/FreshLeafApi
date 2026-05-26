<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $user_id
 * @property int $vendor_inventory_id
 * @property int $cart_status_id
 * @property float $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Cart $cart
 * @property-read User $user
 * @property-read VendorInventory $vendorInventory
 * @property-read CartStatus $status
 */
#[Table('cart_histories', key: 'id', keyType: 'int')]
#[Fillable(['cart_id', 'user_id', 'vendor_inventory_id', 'quantity', 'cart_status_id'])]
class CartHistory extends Model
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    /**
     * Get the cart that owns the history.
     *
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }
}
