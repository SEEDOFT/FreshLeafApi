<?php

namespace App\Models;

use Database\Factories\AdminTypeFactory;
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
 * @method static \Database\Factories\AdminTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<AdminType>|AdminType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<AdminType>|AdminType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<AdminType>|AdminType query()
 * @method static \Illuminate\Database\Eloquent\Builder<AdminType>|AdminType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<AdminType>|AdminType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<AdminType>|AdminType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<AdminType>|AdminType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('admin_types', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(AdminTypeFactory::class)]
class AdminType extends Model
{
    public const SUPER_ADMIN = 1;

    public const OPERATION = 2;

    public const SUPPORT = 3;

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'type_id');
    }
}
