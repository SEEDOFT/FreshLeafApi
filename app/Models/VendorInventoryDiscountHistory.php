<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_inventory_discount_id', 'vendor_inventory_id', 'discount_percentage', 'starts_at', 'ends_at', 'action_type'])]
class VendorInventoryDiscountHistory extends Model
{
    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<VendorInventoryDiscount, $this>
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(VendorInventoryDiscount::class, 'vendor_inventory_discount_id');
    }

    /**
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(VendorInventory::class);
    }
}
