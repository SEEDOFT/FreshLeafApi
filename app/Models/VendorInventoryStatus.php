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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property-read string|null $translated_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table('vendor_inventory_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(VendorInventoryStatusFactory::class)]
class VendorInventoryStatus extends Model
{
    /** @use HasFactory<VendorInventoryStatusFactory> */
    use HasFactory, SoftDeletes;

    public const int AVAILABLE_ID = 1;

    public const int OUT_OF_STOCK_ID = 2;

    public const int HIDDEN_ID = 3;

    /**
     * Get the translated name of the inventory status.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::getLocale()};
    }

    /**
     * Get the inventories that belong to this status.
     *
     * @return HasMany<VendorInventory, $this>
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(
            VendorInventory::class,
            'inventory_status_id',
            'id'
        );
    }
}
