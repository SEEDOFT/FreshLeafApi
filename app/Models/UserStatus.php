<?php

namespace App\Models;

use Database\Factories\UserStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\UserStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserStatus whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class UserStatus extends Model
{
    /** @use HasFactory<UserStatusFactory> */
    use HasFactory;

    /**
     * Get the users for the user status.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
