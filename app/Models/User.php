<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
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
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read UserType $userType
 * @property-read UserStatus $userStatus
 * @property-read UserProfile|null $userProfile
 * @property-read VendorProfile|null $vendorProfile
 * @property-read AdminProfile|null $adminProfile
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
])]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(UserFactory::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
     * Get the user type.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(UserType::class, 'user_type_id', 'id');
    }

    /**
     * Get the user status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(UserStatus::class, 'user_status_id', 'id');
    }

    /**
     * Get the addresses for the user.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class, 'user_id', 'id');
    }

    /**
     * Get the end-user profile.
     */
    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    /**
     * Get the vendor profile.
     */
    public function vendorProfile(): HasOne
    {
        return $this->hasOne(VendorProfile::class, 'user_id', 'id');
    }

    /**
     * Get the admin profile.
     */
    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class, 'user_id', 'id');
    }

    /**
     * Get the payment methods for the user.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(UserPaymentMethod::class, 'user_id', 'id');
    }

    /**
     * Scope a query to a specific user status.
     *
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function withStatus(Builder $query, int $userStatusId): void
    {
        $query->where('user_status_id', $userStatusId);
    }

    /**
     * Scope a query to a specific user type.
     *
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function ofType(Builder $query, int $userTypeId): void
    {
        $query->where('user_type_id', $userTypeId);
    }

    /**
     * Scope a query to active users.
     *
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $this->withStatus($query, UserStatus::ACTIVE);
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
