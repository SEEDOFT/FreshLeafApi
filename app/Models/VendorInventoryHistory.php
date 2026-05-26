<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_inventory_id
 * @property float $quantity_change
 * @property float $new_quantity
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read VendorInventory $vendorInventory
 * @property-read Model|null $reference
 */
#[Table('vendor_inventory_histories', key: 'id')]
#[Fillable([
    'vendor_inventory_id',
    'quantity_change',
    'new_quantity',
    'reference_type',
    'reference_id',
    'reason',
])]
class VendorInventoryHistory extends Model
{
    use SoftDeletes;

    /**
     * Get the vendor inventory this history belongs to.
     *
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(VendorInventory::class, 'vendor_inventory_id', 'id');
    }

    /**
     * Get the reference model (e.g., Order or OrderItem) that caused this history.
     *
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }
}
