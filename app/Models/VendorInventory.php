<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_id
 * @property int $product_id
 * @property float $price
 * @property float $stock_quantity
 * @property int $unit_id
 * @property bool $is_active
 * @property Carbon|null $harvest_date
 * @property string|null $farm_location
 * @property array<string>|null $batch_images
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read User $vendor
 * @property-read Product $product
 * @property-read Unit $unit
 */
#[Table('vendor_inventories', key: 'id')]
#[Fillable([
    'vendor_id',
    'product_id',
    'price',
    'stock_quantity',
    'unit_id',
    'is_active',
    'harvest_date',
    'farm_location',
    'batch_images',
])]
class VendorInventory extends Model
{
    /** @use HasFactory<\Database\Factories\VendorInventoryFactory> */
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
            'is_active' => 'boolean',
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
}
