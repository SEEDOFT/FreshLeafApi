<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property int $vendor_inventory_id
 * @property float $discount_percentage
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read VendorInventory $vendorInventory
 * @property-read Collection<int, VendorInventoryDiscountHistory> $histories
 */
#[Table('vendor_inventory_discounts', key: 'id', keyType: 'int')]
#[Fillable(['vendor_inventory_id', 'discount_percentage', 'starts_at', 'ends_at'])]
class VendorInventoryDiscount extends Model
{
    use SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Get the vendor inventory this discount belongs to.
     *
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(VendorInventory::class, 'vendor_inventory_id', 'id');
    }

    /**
     * Get the histories for this discount.
     *
     * @return HasMany<VendorInventoryDiscountHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(VendorInventoryDiscountHistory::class, 'vendor_inventory_discount_id', 'id');
    }
}
