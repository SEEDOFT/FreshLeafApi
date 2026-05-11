<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorInventoryStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 */
#[Table('vendor_inventory_statuses', key: 'id', keyType: 'int')]
#[Fillable(['code', 'name'])]
#[UseFactory(VendorInventoryStatusFactory::class)]
class VendorInventoryStatus extends Model
{
    /** @use HasFactory<VendorInventoryStatusFactory> */
    use HasFactory;

    public const int ACTIVE = 1;

    public const int INACTIVE = 2;

    public const int OUT_OF_STOCK = 3;

    /**
     * @return HasMany<VendorInventory, $this>
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(VendorInventory::class, 'inventory_status_id', 'id');
    }
}
