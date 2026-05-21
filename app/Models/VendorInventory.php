<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorInventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $vendor_id
 * @property int $product_id
 * @property int $currency_id
 * @property int $inventory_status_id
 * @property float $price
 * @property float $stock_quantity
 * @property int $unit_id
 * @property Carbon|null $harvest_date
 * @property string|null $farm_location
 * @property string|null $province_of_origin
 * @property string|null $certification_type
 * @property int|null $packaging_type_id
 * @property int|null $shelf_life_days
 * @property array<string>|null $batch_images
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $vendor
 * @property-read Product $product
 * @property-read PackagingType|null $packagingType
 * @property-read Currency $currency
 * @property-read Unit $unit
 * @property-read VendorInventoryStatus $status
 */
#[Table('vendor_inventories', key: 'id', keyType: 'int')]
#[Fillable([
    'vendor_id',
    'product_id',
    'currency_id',
    'inventory_status_id',
    'price',
    'stock_quantity',
    'unit_id',
    'harvest_date',
    'farm_location',
    'province_of_origin',
    'certification_type',
    'packaging_type_id',
    'shelf_life_days',
    'batch_images',
])]
#[UseFactory(VendorInventoryFactory::class)]
class VendorInventory extends Model
{
    /** @use HasFactory<VendorInventoryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * {@inheritDoc}
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
        return $this->belongsTo(User::class, 'vendor_id', 'id');
    }

    /**
     * Get the master product from the catalog.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the currency associated with this inventory.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    /**
     * Get the unit associated with this inventory.
     *
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    /**
     * Get the packaging type associated with this inventory.
     *
     * @return BelongsTo<PackagingType, $this>
     */
    public function packagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class, 'packaging_type_id', 'id');
    }

    /**
     * Get the status associated with this inventory.
     *
     * @return BelongsTo<VendorInventoryStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(
            VendorInventoryStatus::class,
            'inventory_status_id',
            'id',
        );
    }

    /**
     * Get the adjustments for this inventory.
     *
     * @return HasMany<InventoryAdjustment, $this>
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    /**
     * Adjust the stock quantity and log it.
     */
    public function adjustStock(
        float $change,
        string $type,
        ?string $reason = null,
        ?string $proofImagePath = null,
        ?string $notes = null,
    ): void {
        DB::transaction(function () use (
            $change,
            $type,
            $reason,
            $proofImagePath,
            $notes,
        ) {
            $this->increment('stock_quantity', $change);

            $this->adjustments()->create([
                'user_id' => auth()->id(),
                'quantity_change' => $change,
                'type' => $type,
                'reason' => $reason,
                'proof_image_path' => $proofImagePath,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Scope a query to only include active inventory items.
     *
     * @param  Builder<VendorInventory>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('inventory_status_id', VendorInventoryStatus::AVAILABLE_ID);
    }
}
