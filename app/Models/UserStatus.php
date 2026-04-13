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

    public const int ACTIVE = 1;

    public const int INACTIVE = 2;

    public const int DELETED = 3;

    /**
     * Get the users for the user status.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
