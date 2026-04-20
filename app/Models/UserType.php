<?php

declare(strict_types=1);

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
 * @property int $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, User> $users
 */
#[Table('user_types', key: 'id')]
#[Fillable(['code', 'name'])]
#[UseFactory(UserTypeFactory::class)]
class UserType extends Model
{
    public const int ADMIN = 1;

    public const int VENDOR = 2;

    public const int USER = 3;

    /**
     * Get the users for this type.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_type_id', 'id');
    }
}
