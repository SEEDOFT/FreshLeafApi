<?php

namespace App\Models;

use Database\Factories\InventoryMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $inventory_batch_id
 * @property int $inventory_movement_type_id
 * @property numeric $quantity
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $note
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read InventoryBatch $batch
 * @property-read User|null $creator
 * @property-read InventoryMovementType $type
 *
 * @method static \Database\Factories\InventoryMovementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereInventoryBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereInventoryMovementTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['inventory_batch_id', 'inventory_movement_type_id', 'quantity', 'reference_type', 'reference_id', 'note', 'created_by'])]
class InventoryMovement extends Model
{
    /** @use HasFactory<InventoryMovementFactory> */
    use HasFactory;

    /**
     * Get the inventory batch for the movement.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    /**
     * Get the inventory movement type.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(InventoryMovementType::class, 'inventory_movement_type_id');
    }

    /**
     * Get the user who created the movement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
