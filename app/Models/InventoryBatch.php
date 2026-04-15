<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\InventoryBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $product_variant_id
 * @property int $supplier_id
 * @property int $inventory_batch_status_id
 * @property string $batch_code
 * @property numeric $received_qty
 * @property numeric $reserved_qty
 * @property numeric $sold_qty
 * @property numeric $damaged_qty
 * @property numeric $expired_qty
 * @property numeric $cost_per_unit
 * @property Carbon|null $expiry_date
 * @property Carbon $received_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, InventoryMovement> $movements
 * @property-read int|null $movements_count
 * @property-read Product|null $product
 * @property-read InventoryBatchStatus $status
 * @property-read Supplier $supplier
 * @property-read ProductVariant $variant
 */
#[Table('inventory_batches', key: 'id')]
#[Fillable([
    'product_id',
    'product_variant_id',
    'supplier_id',
    'inventory_batch_status_id',
    'batch_code',
    'received_qty',
    'reserved_qty',
    'sold_qty',
    'damaged_qty',
    'expired_qty',
    'cost_per_unit',
    'expiry_date',
    'received_at',
])]
#[UseFactory(InventoryBatchFactory::class)]
class InventoryBatch extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_at' => 'datetime',
        ];
    }

    /**
     * Get the product for the batch.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the product variant for the batch.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }

    /**
     * Get the supplier for the batch.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    /**
     * Get the batch status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(InventoryBatchStatus::class, 'inventory_batch_status_id', 'id');
    }

    /**
     * Get the movements for the batch.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_batch_id', 'id');
    }
}
