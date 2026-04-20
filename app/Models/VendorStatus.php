<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorStatusFactory;
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
 * @method static VendorStatusFactory factory($count = null, $state = [])
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
#[Table('vendor_statuses', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(VendorStatusFactory::class)]
class VendorStatus extends Model
{
    public const ACTIVE = 1;

    public const INACTIVE = 2;

    public const PENDING = 3;

    public const SUSPENDED = 4;

    public const REJECTED = 5;

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'status_id', 'id');
    }
}
