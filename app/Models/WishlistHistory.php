<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
#[Table('wishlist_histories', key: 'id', keyType: 'int')]
#[Fillable(['user_id', 'wishlist_id', 'vendor_inventory_id', 'wishlist_status_id'])]
class WishlistHistory extends Model
{
    use SoftDeletes;

    /**
     * Get the wishlist that owns the wishlist.
     *
     * @return BelongsTo<Wishlist, $this>
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class, 'wishlist_id', 'id');
    }
}
