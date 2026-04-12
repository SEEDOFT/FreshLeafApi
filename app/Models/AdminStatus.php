<?php

namespace App\Models;

use Database\Factories\AdminStatusFactory;
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
 * @property-read Collection<int, Admin> $admins
 * @property-read int|null $admins_count
 *
 * @method static \Database\Factories\AdminStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<AdminStatus>|AdminStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<AdminStatus>|AdminStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<AdminStatus>|AdminStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<AdminStatus>|AdminStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<AdminStatus>|AdminStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<AdminStatus>|AdminStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<AdminStatus>|AdminStatus whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('admin_statuses', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(AdminStatusFactory::class)]
class AdminStatus extends Model
{
    public const ACTIVE = 1;

    public const INACTIVE = 2;

    public const PENDING = 3;

    public const SUSPENDED = 4;

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'status_id');
    }
}
