<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\InventoryMovementTypeFactory;
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
 * @property-read Collection<int, InventoryMovement> $inventoryMovements
 * @property-read int|null $inventory_movements_count
 */
#[Table('inventory_movement_types', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(InventoryMovementTypeFactory::class)]
class InventoryMovementType extends Model
{
    use HasFactory;

    /**
     * Get the inventory movements for the type.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_movement_type_id', 'id');
    }
}
