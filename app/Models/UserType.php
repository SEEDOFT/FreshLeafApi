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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $code
 * @property string $name_en
 * @property string $name_km
 * @property string $translated_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, User> $users
 * @property Carbon|null $deleted_at
 */
#[Table('user_types', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['code', 'name_en', 'name_km'])]
#[UseFactory(UserTypeFactory::class)]
class UserType extends Model
{
    use SoftDeletes;

    public const int ADMIN_ID = 1;

    public const int VENDOR_ID = 2;

    public const int CONSUMER_ID = 3;

    public const string ADMIN = 'ADMIN';

    public const string VENDOR = 'VENDOR';

    public const string CONSUMER = 'CONSUMER';

    /**
     * Get the users for this type.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_type_id', 'id');
    }

    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::currentLocale()};
    }

    public function getColor(): string
    {
        return match ($this->id) {
            self::ADMIN_ID => 'danger',
            self::VENDOR_ID => 'warning',
            self::CONSUMER_ID => 'success',
            default => 'gray',
        };
    }
}
