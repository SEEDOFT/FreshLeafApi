<?php

namespace App\Models;

use Database\Factories\UserTypeFactory;
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
 * @property-read Collection<int, User> $users
 * @property-read int|null $users
 *
 * @method static \Database\Factories\UserTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserType whereUpdatedAt($value)
 *
 * @property-read int|null $users_count
 *
 * @mixin \Eloquent
 */
#[Table('user_types', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(UserTypeFactory::class)]
class UserType extends Model
{
    public const CONSUMER = 1;

    public const OPERATION = 2;

    public const VENDOR = self::OPERATION;

    public const ADMIN = 3;

    /**
     * Get the users for the user type.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
