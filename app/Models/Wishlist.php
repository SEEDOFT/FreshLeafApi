<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $wishlist_status_id
 * @property int $vendor_inventory_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @property-read WishlistStatus $status
 */
#[Table('wishlists', key: 'id', keyType: 'int')]
#[Fillable(['user_id', 'vendor_inventory_id', 'wishlist_status_id'])]
class Wishlist extends Model
{
    use SoftDeletes;

    /**
     * Get the user that owns the wishlist.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the status that owns the wishlist.
     *
     * @return BelongsTo<WishlistStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(
            WishlistStatus::class,
            'wishlist_status_id',
            'id'
        );
    }

    /**
     * Get the vendor inventory attached to this cart row.
     *
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(
            VendorInventory::class,
            'vendor_inventory_id',
            'id'
        );
    }

    /**
     * Get the wishlist histories.
     *
     * @return HasMany<WishlistHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(WishlistHistory::class, 'wishlist_id', 'id');
    }

    /**
     * Active Wishlist
     *
     * @param  Builder<Wishlist>  $query
     */
    #[Scope]
    protected function active(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId)
            ->where('wishlist_status_id', WishlistStatus::ACTIVE_ID);
    }

    /**
     * Must be active wishlist
     */
    public function isActive(): bool
    {
        return $this->wishlist_status_id === WishlistStatus::ACTIVE_ID;
    }
}
