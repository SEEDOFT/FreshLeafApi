<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
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
use Override;

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
 * @property-read Address[]|HasMany<Address, $this> $addresses
 * @property-read PaymentMethod[]|HasMany<PaymentMethod, $this> $paymentMethods
 * @property-read Wallet[]|HasMany<Wallet, $this> $wallets
 * @property-read UserDevice[]|HasMany<UserDevice, $this> $devices
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
class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Determine whether the user can access the given panel.
     */
    #[Override]
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isType(UserType::ADMIN);
        }

        if ($panel->getId() === 'vendor') {
            return $this->isType(UserType::VENDOR);
        }

        return false;
    }

    /**
     * Get the user's current locale from their profile.
     */
    public string $currentLocale {
        get {
            $profile = match ($this->user_type_id) {
                UserType::ADMIN => $this->adminProfile,
                UserType::VENDOR => $this->vendorProfile,
                UserType::USER => $this->userProfile,
                default => null,
            };

            return ($profile !== null) ? $profile->locale : (string) config('app.locale');
        }
    }

    /**
     * Get the user's name for Filament.
     */
    #[Override]
    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * {@inheritDoc}
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
     * @return HasMany<Address, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'user_id', 'id');
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
     * @return HasMany<PaymentMethod, $this>
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'user_id', 'id');
    }

    /**
     * Get wallets for the user.
     *
     * @return HasMany<Wallet, $this>
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'user_id', 'id');
    }

    /**
     * Get the devices for the user.
     *
     * @return HasMany<UserDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class, 'user_id', 'id');
    }

    /**
     * Specifies the user's FCM tokens for notifications.
     *
     * @return array<int, string>
     */
    public function routeNotificationForFcm(): array
    {
        return $this->devices()
            ->where('is_active', true)
            ->pluck('device_token')
            ->all();
    }

    /**
     * Ensure the user has default wallets for KHR and USD.
     */
    public function ensureDefaultWallets(): void
    {
        $khrCurrencyId = (int) Currency::where('code', Currency::KHR)
            ->value('id');
        $usdCurrencyId = (int) Currency::where('code', Currency::USD)
            ->value('id');

        if ($khrCurrencyId <= 0 || $usdCurrencyId <= 0) {
            return;
        }

        $khrWallet = $this->wallets()->firstOrCreate(
            ['user_id' => $this->id, 'currency_id' => $khrCurrencyId],
            ['balance' => '0.00'],
        );

        $freshKhrWallet = $khrWallet->fresh();

        WalletHistory::insert([
            'user_id' => $this->id,
            'wallet_id' => $freshKhrWallet->id,
            'currency_id' => $khrCurrencyId,
            'balance' => $freshKhrWallet->balance,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usdWallet = $this->wallets()->firstOrCreate(
            ['user_id' => $this->id, 'currency_id' => $usdCurrencyId],
            ['balance' => '0.00'],
        );

        $freshUsdWallet = $usdWallet->fresh();

        WalletHistory::insert([
            'user_id' => $this->id,
            'wallet_id' => $freshUsdWallet->id,
            'currency_id' => $usdCurrencyId,
            'balance' => $freshUsdWallet->balance,
            'created_at' => now(),
            'updated_at' => now(),
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
