<?php

namespace App\Models;

use Database\Factories\InventoryBatchStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
 * @method static \Database\Factories\InventoryBatchStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatchStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatchStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatchStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatchStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatchStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatchStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatchStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatchStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class InventoryBatchStatus extends Model
{
    /** @use HasFactory<InventoryBatchStatusFactory> */
    use HasFactory;

    /**
     * Get the inventory batches for the status.
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }
}
