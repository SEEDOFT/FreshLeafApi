<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property int $id
 * @property string $first_name
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $phone_number
 * @property int|null $user_type_id
 * @property int|null $user_status_id
 * @property Carbon|null $deleted_at
 * @property string $last_name
 * @property string|null $image
 * @property-read Collection<int, UserAddress> $addresses
 * @property-read int|null $addresses_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read UserStatus|null $status
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read UserType|null $type
 * @property-read Collection<int, UserPaymentMethod> $paymentMethods
 * @property-read int|null $paymentMethods_count
 * @property-read VendorProfile|null $vendorProfile
 * @property-read AdminProfile|null $adminProfile
 * @property-read ConsumerProfile|null $consumerProfile
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereImage($value)
 *
 * @property-read int|null $payment_methods_count
 * @property string|null $pin
 *
 * @method static Builder<static>|User active()
 * @method static Builder<static>|User ofType(int $userTypeId)
 * @method static Builder<static>|User wherePin($value)
 * @method static Builder<static>|User withStatus(int $userStatusId)
 *
 * @property-read VendorProfile|null $vendorProfile
 *
 * @mixin \Eloquent
 */
#[Table('users', key: 'id')]
#[Appends(['image'])]
#[Fillable([
    'first_name',
    'last_name',
    'email',
    'image',
    'phone_number',
    'password',
    'user_type_id',
    'user_status_id',
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
     * Get the vendor account for the user.
     */
    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    /**
     * Get the admin account for the user.
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Get the vendor profile for the user.
     */
    public function vendorProfile(): HasOne
    {
        return $this->hasOne(VendorProfile::class);
    }

    /**
     * Get the admin profile for the user.
     */
    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    /**
     * Get the consumer profile for the user.
     */
    public function consumerProfile(): HasOne
    {
        return $this->hasOne(ConsumerProfile::class);
    }

    /**
     * Get persisted panel preferences for the user.
     */
    public function preference(): MorphOne
    {
        return $this->morphOne(PanelPreference::class, 'account');
    }

    /**
     * Scope a query to a specific user type.
     */
    public function scopeOfType(Builder $query, int $userTypeId): Builder
    {
        return $query->where('user_type_id', $userTypeId);
    }

    /**
     * Scope a query to a specific user status.
     */
    public function scopeWithStatus(Builder $query, int $userStatusId): Builder
    {
        return $query->where('user_status_id', $userStatusId);
    }

    /**
     * Scope a query to active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->withStatus(UserStatus::ACTIVE);
    }

    /**
     * Determine whether the user has the given type.
     */
    public function isType(int $userTypeId): bool
    {
        return (int) $this->user_type_id === $userTypeId;
    }

    /**
     * Determine whether the user is active.
     */
    public function isActive(): bool
    {
        return (int) $this->user_status_id === UserStatus::ACTIVE;
    }
}
