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

#[Table('vendor_profiles', key: 'id')]
#[Fillable([
    'user_id',
    'business_name',
    'contact_phone',
    'city',
    'province',
    'address',
    'is_verified',
    'approved_at',
    'rejected_at',
    'approved_by_admin_id',
    'rejected_by_admin_id',
    'approve_reason',
    'reject_reason',
    'meta',
    'id_card_front',
    'id_card_back',
    'store_front_image',
    'organic_certificate_url',
    'bank_name',
    'bank_account_name',
    'bank_account_number',
    'bank_qr_code',
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
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'approved_by_admin_id' => 'integer',
            'rejected_by_admin_id' => 'integer',
            'meta' => 'array',
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
}
