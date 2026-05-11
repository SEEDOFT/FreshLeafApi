<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_wishlist_id
 * @property int $vendor_inventory_id
 * @property int $user_wishlist_item_status_id
 * @property int $user_wishlist_item_type_id
 * @property-read Wishlist $wishlist
 * @property-read VendorInventory $vendorInventory
 * @property-read UserWishlistItemStatus $status
 * @property-read UserWishlistItemType $type
 */
#[Table('user_wishlist_items', key: 'id', keyType: 'int')]
#[Fillable([
    'user_wishlist_id',
    'vendor_inventory_id',
    'user_wishlist_item_status_id',
    'user_wishlist_item_type_id',
])]
class WishlistItem extends Model
{
    /**
     * Get the wishlist that owns the wishlist item.
     *
     * @return BelongsTo<Wishlist, $this>
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class, 'user_wishlist_id');
    }

    /**
     * Get the vendor inventory that owns the wishlist item.
     *
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(VendorInventory::class, 'vendor_inventory_id', 'id');
    }

    /**
     * Get the status that owns the wishlist item.
     *
     * @return BelongsTo<UserWishlistItemStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(
            UserWishlistItemStatus::class,
            'user_wishlist_item_status_id',
            'id'
        );
    }

    /**
     * Get the type that owns the wishlist item.
     *
     * @return BelongsTo<UserWishlistItemType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(
            UserWishlistItemType::class,
            'user_wishlist_item_type_id',
            'id'
        );
    }
}
