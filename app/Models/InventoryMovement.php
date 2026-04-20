<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\InventoryMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
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
 */
#[Table('inventory_movements', key: 'id')]
#[Fillable([
    'inventory_batch_id',
    'inventory_movement_type_id',
    'quantity',
    'reference_type',
    'reference_id',
    'note',
    'created_by',
])]
#[UseFactory(InventoryMovementFactory::class)]
class InventoryMovement extends Model
{
    use HasFactory;

    /**
     * Get the inventory batch for the movement.
     */
    /**
     * @return BelongsTo<InventoryBatch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id', 'id');
    }

    /**
     * Get the inventory movement type.
     */
    /**
     * @return BelongsTo<InventoryMovementType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(InventoryMovementType::class, 'inventory_movement_type_id', 'id');
    }

    /**
     * Get the user who created the movement.
     */
    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
