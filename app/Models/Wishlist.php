<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('user_wishlists', key: 'id', keyType: 'int')]
#[Fillable([
    'user_id',
    'user_wishlist_status_id',
    'user_wishlist_type_id',
])]
class Wishlist extends Model
{
    /**
     * Get the user that owns the wishlist.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the wishlist.
     *
     * @return HasMany<WishlistItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class, 'user_wishlist_id');
    }

    /**
     * Get the status that owns the wishlist.
     *
     * @return BelongsTo<UserWishlistStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(UserWishlistStatus::class, 'user_wishlist_status_id');
    }

    /**
     * Get the type that owns the wishlist.
     *
     * @return BelongsTo<UserWishlistType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(UserWishlistType::class, 'user_wishlist_type_id');
    }
}
