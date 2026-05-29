<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property string|null $translated_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, User> $users
 * @property Carbon|null $deleted_at
 */
#[Table('user_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(UserStatusFactory::class)]
class UserStatus extends Model
{
    /** @use HasFactory<UserStatusFactory> */
    use HasFactory, SoftDeletes;

    public const int PENDING_ID = 1;

    public const int ACTIVE_ID = 2;

    public const int INACTIVE_ID = 3;

    public const int DELETED_ID = 4;

    public const int REJECTED_ID = 5;

    /**
     * Get the users for this status.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_status_id', 'id');
    }

    /**
     * Get the translated name of the status.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::currentLocale()};
    }
}
