<?php

namespace App\Models;

use Database\Factories\VendorTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Vendor> $vendors
 * @property-read int|null $vendors_count
 * @method static \Database\Factories\VendorTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<VendorType>|VendorType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<VendorType>|VendorType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<VendorType>|VendorType query()
 * @method static \Illuminate\Database\Eloquent\Builder<VendorType>|VendorType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<VendorType>|VendorType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<VendorType>|VendorType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<VendorType>|VendorType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Table('vendor_types', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(VendorTypeFactory::class)]
class VendorType extends Model
{
    public const STANDART = 1;

    public const PREMIUM = 2;

    public const ENTERPRISE = 3;

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'type_id');
    }
}
