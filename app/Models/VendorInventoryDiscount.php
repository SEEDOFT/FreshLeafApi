<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_inventory_id', 'discount_percentage', 'starts_at', 'ends_at'])]
class VendorInventoryDiscount extends Model
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
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(VendorInventory::class);
    }

    /**
     * @return HasMany<VendorInventoryDiscountHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(VendorInventoryDiscountHistory::class);
    }
}
