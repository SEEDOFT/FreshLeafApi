<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorInventoryStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 */
class VendorInventoryStatus extends Model
{
    /** @use HasFactory<VendorInventoryStatusFactory> */
    use HasFactory;

    protected $fillable = ['code', 'name'];

    public const int ACTIVE = 1;

    public const int INACTIVE = 2;

    public const int OUT_OF_STOCK = 3;

    /**
     * @return HasMany<VendorInventory, $this>
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(VendorInventory::class, 'inventory_status_id');
    }
}
