<?php

namespace App\Models;

use Database\Factories\UserStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, User> $users
 */
#[Table('user_statuses', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(UserStatusFactory::class)]
class UserStatus extends Model
{
    use HasFactory;

    public const ACTIVE = 1;

    public const INACTIVE = 2;

    public const DELETED = 3;

    public const PENDING = 4;

    /**
     * Get the users for the user status.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
