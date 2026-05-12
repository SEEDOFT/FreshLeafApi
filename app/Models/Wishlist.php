<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $wishlist_status_id
 * @property int $vendor_inventory_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read WishlistStatus $status
 */
#[Table('wishlists', key: 'id', keyType: 'int')]
#[Fillable(['user_id', 'vendor_inventory_id', 'wishlist_status_id'])]
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
}
