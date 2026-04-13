<?php

namespace App\Models;

use Database\Factories\InventoryBatchStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, InventoryBatch> $inventoryBatches
 * @property-read int|null $inventory_batches_count
 */
#[Table('inventory_batch_statuses', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(InventoryBatchStatusFactory::class)]
class InventoryBatchStatus extends Model
{
    use HasFactory;

    /**
     * Get the inventory batches for the status.
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }
}
