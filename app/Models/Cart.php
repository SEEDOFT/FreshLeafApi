<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $user_cart_status_id
 * @property int $user_cart_type_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, CartItem> $items
 * @property-read User $user
 * @property-read UserCartStatus $status
 * @property-read UserCartType $type
 */
#[Table('user_carts', key: 'id', keyType: 'int')]
#[Fillable([
    'user_id',
    'user_cart_status_id',
    'user_cart_type_id',
])]
#[UseFactory(CartFactory::class)]
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

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
     * Get the items for the cart.
     *
     * @return HasMany<CartItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    /**
     * Get the status that owns the cart.
     *
     * @return BelongsTo<UserCartStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(UserCartStatus::class, 'user_cart_status_id');
    }

    /**
     * Get the type that owns the cart.
     *
     * @return BelongsTo<UserCartType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(UserCartType::class, 'user_cart_type_id');
    }
}
