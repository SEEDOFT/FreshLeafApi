<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
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
 *
 * @method static VendorTypeFactory factory($count = null, $state = [])
 * @method static Builder<self> newModelQuery()
 * @method static Builder<self> newQuery()
 * @method static Builder<self> query()
 * @method static Builder<self> whereCreatedAt($value)
 * @method static Builder<self> whereId($value)
 * @method static Builder<self> whereName($value)
 * @method static Builder<self> whereUpdatedAt($value)
 *
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
        return $this->hasMany(Vendor::class, 'type_id', 'id');
    }
}
