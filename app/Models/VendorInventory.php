<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorInventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_id
 * @property int $product_id
 * @property int $inventory_status_id
 * @property float $price
 * @property float $stock_quantity
 * @property int $unit_id
 * @property Carbon|null $harvest_date
 * @property string|null $farm_location
 * @property string|null $province_of_origin
 * @property string|null $certification_type
 * @property string|null $packaging_type
 * @property int|null $shelf_life_days
 * @property array<string>|null $batch_images
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $vendor
 * @property-read Product $product
 * @property-read Unit $unit
 * @property-read VendorInventoryStatus $status
 */
#[Table('vendor_inventories', key: 'id')]
#[Fillable([
    'vendor_id',
    'product_id',
    'inventory_status_id',
    'price',
    'stock_quantity',
    'unit_id',
    'harvest_date',
    'farm_location',
    'province_of_origin',
    'certification_type',
    'packaging_type',
    'shelf_life_days',
    'batch_images',
])]
class VendorInventory extends Model
{
    /** @use HasFactory<VendorInventoryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'decimal:2',
            'harvest_date' => 'date',
            'batch_images' => 'array',
        ];
    }

    /**
     * Get the vendor that owns the inventory.
     *
     * @return BelongsTo<User, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the master product from the catalog.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the unit associated with this inventory.
     *
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get the status associated with this inventory.
     *
     * @return BelongsTo<VendorInventoryStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(VendorInventoryStatus::class, 'inventory_status_id');
    }

    /**
     * Scope a query to only include active inventory items.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active($query): void
    {
        $query->where('inventory_status_id', VendorInventoryStatus::ACTIVE);
    }
}
