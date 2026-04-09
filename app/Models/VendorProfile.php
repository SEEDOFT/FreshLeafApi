<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $business_name
 * @property string|null $contact_phone
 * @property string|null $city
 * @property string|null $province
 * @property string|null $address
 * @property bool $is_verified
 * @property array<array-key, mixed>|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorProfile whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'business_name',
    'contact_phone',
    'city',
    'province',
    'address',
    'is_verified',
    'meta',
])]
class VendorProfile extends Model
{
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
