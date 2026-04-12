<?php

namespace App\Models;

use Database\Factories\InventoryMovementTypeFactory;
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
 * @property-read Collection<int, InventoryMovement> $inventoryMovements
 * @property-read int|null $inventory_movements_count
 *
 * @method static \Database\Factories\InventoryMovementTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovementType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovementType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovementType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovementType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovementType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovementType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovementType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovementType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class InventoryMovementType extends Model
{
    /** @use HasFactory<InventoryMovementTypeFactory> */
    use HasFactory;

    /**
     * Get the inventory movements for the type.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
