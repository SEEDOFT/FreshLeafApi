<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $addressable_id
 * @property string|null $addressable_type
 * @property int|null $user_id
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
 * @property Carbon|null $deleted_at
 */
#[Table('addresses', key: 'id', keyType: 'int')]
#[Fillable([
    'addressable_type',
    'addressable_id',
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
#[UseFactory(AddressFactory::class)]
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the parent addressable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo('addressable', 'addressable_type', 'addressable_id');
    }
}
