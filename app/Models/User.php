<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $image
 * @property string $phone_number
 * @property string $password
 * @property int $user_type_id
 * @property int $user_status_id
 * @property string|null $pin
 * @property string|null $preferred_language
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read UserType $userType
 * @property-read UserStatus $userStatus
 * @property-read UserAddress[]|HasMany $addresses
 * @property-read UserPaymentMethod[]|HasMany $paymentMethods
 *
 * @method static Builder<static>|User ofType(int $userTypeId)
 * @method static Builder<static>|User withStatus(int $userStatusId)
 * @method static Builder<static>|User active()
 */
#[Table('users', key: 'id', keyType: 'int')]
#[Fillable([
    'first_name',
    'last_name',
    'email',
    'image',
    'phone_number',
    'password',
    'user_type_id',
    'user_status_id',
    'pin',
    'preferred_language',
])]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(UserFactory::class)]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pin' => 'hashed',
            'preferred_language' => 'string',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the public profile image URL for the user, if available.
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if (! $value) {
                    return null;
                }
                if (Str::startsWith($value, ['http://', 'https://'])) {
                    return $value;
                }

                $path = "users/{$value}";

                return Storage::disk('public')->url($path);
            },
            set: fn (?string $value) => $value,
        );
    }

    /**
     * Get the user type.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    /**
     * Get the user status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(UserStatus::class, 'user_status_id');
    }

    /**
     * Get the addresses for the user.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Get the payment methods for the user.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(UserPaymentMethod::class);
    }

    /**
     * Scope a query to a specific user type.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    public function ofType(Builder $query, int $userTypeId): Builder
    {
        return $query->where('user_type_id', $userTypeId);
    }

    /**
     * Scope a query to a specific user status.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    public function withStatus(Builder $query, int $userStatusId): Builder
    {
        return $query->where('user_status_id', $userStatusId);
    }

    /**
     * Scope a query to active users.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    public function active(Builder $query): Builder
    {
        return $query->withStatus(UserStatus::ACTIVE);
    }

    /**
     * Determine whether the user has the given type.
     */
    public function isType(int $userTypeId): bool
    {
        return $this->user_type_id === $userTypeId;
    }

    /**
     * Determine whether the user is active.
     */
    public function isActive(): bool
    {
        return $this->user_status_id === UserStatus::ACTIVE;
    }
}
