<?php

namespace App\Models;

use Database\Factories\AddressFactory;
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
 * @method static \Database\Factories\AddressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereRecipientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUserId($value)
 * @property Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address withoutTrashed()
 * @property numeric|null $lat
 * @property numeric|null $long
 * @property string|null $address_map
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereAddressMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLong($value)
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
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
