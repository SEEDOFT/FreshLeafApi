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
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
 * @property-read Address[]|MorphMany<Address, $this> $addresses
 * @property-read UserPaymentMethod[]|HasMany<UserPaymentMethod, $this> $paymentMethods
 * @property-read Wallet|null $wallet
 * @property-read Wallet[]|MorphMany<Wallet, $this> $wallets
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
     *
     * @return BelongsTo<UserType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(UserType::class, 'user_type_id', 'id');
    }

    /**
     * Get the user status.
     *
     * @return BelongsTo<UserStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(UserStatus::class, 'user_status_id', 'id');
    }

    /**
     * Get the addresses for the user.
     *
     * @return MorphMany<Address, $this>
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the end-user profile.
     *
     * @return HasOne<UserProfile, $this>
     */
    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    /**
     * Get the vendor profile.
     *
     * @return HasOne<VendorProfile, $this>
     */
    public function vendorProfile(): HasOne
    {
        return $this->hasOne(VendorProfile::class, 'user_id', 'id');
    }

    /**
     * Get the admin profile.
     *
     * @return HasOne<AdminProfile, $this>
     */
    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class, 'user_id', 'id');
    }

    /**
     * Get the payment methods for the user.
     *
     * @return HasMany<UserPaymentMethod, $this>
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(UserPaymentMethod::class, 'user_id', 'id');
    }

    /**
     * Get wallets for the user.
     *
     * @return MorphMany<Wallet, $this>
     */
    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'walletable');
    }

    /**
     * Ensure the user has default wallets for KHR and USD.
     */
    public function ensureDefaultWallets(): void
    {
        $this->wallets()->create([
            'balance' => '0.00',
            'currency_id' => Currency::KHR,
        ]);

        $this->wallets()->create([
            'balance' => '0.00',
            'currency_id' => Currency::USD,
        ]);
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
        $query->withStatus(UserStatus::ACTIVE);
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
