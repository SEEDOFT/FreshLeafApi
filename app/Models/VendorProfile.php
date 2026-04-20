<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Table('vendor_profiles', key: 'id')]
#[Fillable([
    'user_id',
    'business_name',
    'contact_phone',
    'city',
    'province',
    'user_address_id',
    'is_verified',
])]
#[UseFactory(VendorProfileFactory::class)]
class VendorProfile extends Model
{
    /** @use HasFactory<VendorProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the vendor profile.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get all addresses for the vendor profile.
     *
     * @return MorphMany<Address, $this>
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the default wallet for the vendor profile.
     *
     * @return MorphOne<Wallet, $this>
     */
    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'walletable')
            ->where('is_default', true);
    }

    /**
     * Get all wallets for the vendor profile.
     *
     * @return MorphMany<Wallet, $this>
     */
    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'walletable');
    }
}
