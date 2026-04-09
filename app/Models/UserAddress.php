<?php

namespace App\Models;

use Database\Factories\UserAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $label
 * @property string $recipient_name
 * @property string $phone
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $city
 * @property string $province
 * @property string $postal_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Database\Factories\UserAddressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereRecipientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereUpdatedAt($value)
 *
 * @property Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress withoutTrashed()
 *
 * @property numeric|null $lat
 * @property numeric|null $long
 * @property string|null $address_map
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereAddressMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereLong($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'label',
    'recipient_name',
    'phone',
    'address_line_1',
    'address_line_2',
    'city',
    'province',
    'postal_code',
    'lat',
    'long',
    'address_map',
])]
class UserAddress extends Model
{
    /** @use HasFactory<UserAddressFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
