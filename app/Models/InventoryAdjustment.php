<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('inventory_adjustments', key: 'id', keyType: 'int')]
#[Fillable([
    'vendor_inventory_id',
    'user_id',
    'quantity_change',
    'type',
    'reason',
    'proof_image_path',
    'notes',
])]
class InventoryAdjustment extends Model
{
    /**
     * Get the vendor inventory that owns the adjustment.
     *
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(VendorInventory::class);
    }

    /**
     * Get the user who made the adjustment.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
