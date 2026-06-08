<?php

declare(strict_types=1);

namespace App\Models;

use App\Constants\StorageDirectory;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
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
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Override;

use function array_filter;
use function array_values;
use function is_string;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string $image
 * @property string $phone_number
 * @property string $password
 * @property int $user_type_id
 * @property int $user_status_id
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read UserProfile $userProfile
 * @property-read VendorProfile $vendorProfile
 * @property-read AdminProfile $adminProfile
 * @property-read UserType $type
 * @property-read UserStatus $status
 * @property-read string $fullName
 * @property-read string $currentLocale
 * @property-read string $currentTheme
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, PaymentMethod> $paymentMethods
 * @property-read PaymentMethod $vendorFinancialDetails
 * @property-read Collection<int, Wallet> $wallets
 * @property-read Collection<int, UserDevice> $devices
 */
#[Table('users', key: 'id', keyType: 'int')]
#[Fillable([
    'first_name',
    'last_name',
    'email',
    'email_verified_at',
    'phone_number',
    'phone_number_verified_at',
    'image',
    'password',
    'user_type_id',
    'user_status_id',
])]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(UserFactory::class)]
class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public const string DEFAULT_PROFILE = StorageDirectory::USERS.'/'.'user.png';

    #[Override]
    public function getFilamentAvatarUrl(): string
    {
        return Storage::url($this->image);
    }

    /**
     * Determine whether the user can access the given panel.
     */
    #[Override]
    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if (strtoupper($panel->getId()) === UserType::ADMIN) {
            return $this->isType(UserType::ADMIN_ID);
        }

        if (strtoupper($panel->getId()) === UserType::VENDOR) {
            return $this->isType(UserType::VENDOR_ID);
        }

        return false;
    }

    /**
     * Get the user's current locale from their profile.
     */
    public string $currentLocale {
        get => match (true) {
            $this->isType(UserType::ADMIN_ID) => $this->adminProfile->locale ?? config('app.locale'),
            $this->isType(UserType::VENDOR_ID) => $this->vendorProfile->locale ?? config('app.locale'),
            default => $this->userProfile->locale ?? config('app.locale'),
        };
    }

    /**
     * Get the user's current theme from their profile.
     */
    public string $currentTheme {
        get => match (true) {
            $this->isType(UserType::ADMIN_ID) => $this->adminProfile->theme ?? config('app.theme'),
            $this->isType(UserType::VENDOR_ID) => $this->vendorProfile->theme ?? config('app.theme'),
            default => $this->userProfile->theme ?? config('app.theme'),
        };
    }

    /**
     * Get the user's full name.
     */
    public string $fullName {
        get => "{$this->last_name} {$this->first_name}";
    }

    /**
     * Get the user's name for Filament.
     */
    #[Override]
    public function getFilamentName(): string
    {
        return "{$this->last_name} {$this->first_name}";
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
            'phone_number_verified_at' => 'datetime',
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
     * Get vendor financial details.
     *
     * @return HasOne<PaymentMethod, $this>
     */
    public function vendorFinancialDetails(): HasOne
    {
        return $this->hasOne(PaymentMethod::class, 'user_id', 'id')
            ->whereHas('vendor', function (Builder $query): void {
                $query->where('user_type_id', UserType::VENDOR_ID);
            });
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
     * Get the vendor inventories for the vendor.
     *
     * @return HasMany<VendorInventory, $this>
     */
    public function vendorInventories(): HasMany
    {
        return $this->hasMany(VendorInventory::class, 'vendor_id', 'id');
    }

    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'App.Models.User.'.$this->id;
    }

    /**
     * Get the entity's notifications.
     *
     * @return MorphMany<FilamentNotification, $this>
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(FilamentNotification::class, 'notifiable')->latest();
    }

    /**
     * Specifies the user's FCM tokens for notifications.
     *
     * @return list<string>
     */
    public function routeNotificationForFcm(): array
    {
        $tokens = $this->devices()
            ->where('is_active', true)
            ->pluck('device_token')
            ->toArray();

        /** @var list<string> $filteredTokens */
        $filteredTokens = array_values(
            array_filter(
                $tokens,
                static fn (mixed $token): bool => is_string($token)
            )
        );

        return $filteredTokens;
    }

    /**
     * Ensure the user has default wallets for KHR and USD.
     */
    public function ensureDefaultWallets(): void
    {
        foreach ([Currency::KHR_ID, Currency::USD_ID] as $currencyId) {
            $wallet = $this->wallets()->firstOrCreate(
                ['user_id' => $this->id, 'currency_id' => $currencyId],
                ['balance' => '0.00'],
            );

            WalletHistory::insert([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'currency_id' => $wallet->currency_id,
                'balance' => $wallet->balance,
                'created_at' => $wallet->created_at,
                'updated_at' => $wallet->updated_at,
            ]);
        }
    }

    /**
     * Ensure the user has default payment methods.
     */
    public function ensureDefaultPaymentMethod(): void
    {
        foreach ([
            PaymentMethod::WALLET_ID,
            PaymentMethod::ABA_ID,
            PaymentMethod::ACLEDA_ID,
            PaymentMethod::COD_ID,
        ] as $paymentMethodId) {
            PaymentMethod::create([
                'user_id' => $this->id,
                'payment_method_type_id' => $paymentMethodId,
                'payment_method_status_id' => PaymentMethodStatus::ACTIVE_ID,
            ]);
        }
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
        $query->withStatus(UserStatus::ACTIVE_ID);
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
        return (int) $this->user_status_id === UserStatus::ACTIVE_ID;
    }
}
