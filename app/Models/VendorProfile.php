<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $locale
 * @property string $theme
 * @property string $business_name
 * @property string $shop_description
 * @property string $contact_phone
 * @property string $village
 * @property string $commune
 * @property string $district
 * @property string $province
 * @property string $opening_time
 * @property string $closing_time
 * @property bool $is_open
 * @property bool $is_verified
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property int|null $approved_by_admin_id
 * @property int|null $rejected_by_admin_id
 * @property string|null $approve_reason
 * @property string|null $reject_reason
 * @property string|null $id_card_front
 * @property string|null $id_card_back
 * @property string|null $store_front_image
 * @property string|null $organic_certificate_url
 * @property-read string $address
 * @property-read User $user
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
#[Table('vendor_profiles', key: 'id', keyType: 'int')]
#[Fillable([
    'user_id',
    'locale',
    'theme',
    'business_name',
    'shop_description',
    'contact_phone',
    'village',
    'commune',
    'district',
    'province',
    'opening_time',
    'closing_time',
    'is_open',
    'is_verified',
    'approved_at',
    'rejected_at',
    'approved_by_admin_id',
    'rejected_by_admin_id',
    'approve_reason',
    'reject_reason',
    'id_card_front',
    'id_card_back',
    'store_front_image',
    'organic_certificate_url',
])]
#[UseFactory(VendorProfileFactory::class)]
class VendorProfile extends Model
{
    use SoftDeletes;

    /** @use HasFactory<VendorProfileFactory> */
    use HasFactory;

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'is_verified' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'approved_by_admin_id' => 'integer',
            'rejected_by_admin_id' => 'integer',
        ];
    }

    /**
     * Get the verification status of the vendor.
     */
    public public(set) string $verificationStatus {
        get {
            if ($this->is_verified) {
                return 'Verified';
            }

            if ($this->rejected_at) {
                return 'Rejected';
            }

            if ($this->id_card_front) {
                return 'Pending Review';
            }

            return 'Unverified';
        }
    }

    /**
     * Determine if the vendor is currently eligible to sell products.
     */
    public public(set) bool $canSell {
        get => $this->is_verified && ! $this->rejected_at;
    }

    /**
     * Get the full address of the vendor.
     */
    public public(set) string $address {
        get => implode(', ', array_filter([
            $this->village,
            $this->commune,
            $this->district,
            $this->province,
        ]));
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
}
