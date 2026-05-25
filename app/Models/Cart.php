<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $vendor_inventory_id
 * @property int $cart_status_id
 * @property float $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read VendorInventory $vendorInventory
 * @property-read CartStatus $status
 */
#[Table('carts', key: 'id', keyType: 'int')]
#[Fillable(['user_id', 'vendor_inventory_id', 'quantity', 'cart_status_id'])]
#[UseFactory(CartFactory::class)]
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory, SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the cart.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the vendor inventory attached to this cart row.
     *
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(VendorInventory::class, 'vendor_inventory_id', 'id');
    }

    /**
     * Get the status that owns the cart.
     *
     * @return BelongsTo<CartStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(CartStatus::class, 'cart_status_id', 'id');
    }

    /**
     * Active Cart
     *
     * @param  Builder<Cart>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('cart_status_id', CartStatus::ACTIVE_ID);
    }
}
