<?php

namespace App\Models;

use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property int $type_id
 * @property int $status_id
 * @property string|null $business_name
 * @property string|null $contact_phone
 * @property string|null $city
 * @property string|null $province
 * @property string|null $address
 * @property bool $is_verified
 * @property array|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read VendorType $type
 * @property-read VendorStatus $status
 */
#[Table('vendors', key: 'id')]
#[Fillable([
    'first_name',
    'last_name',
    'email',
    'password',
    'type_id',
    'status_id',
    'business_name',
    'contact_phone',
    'city',
    'province',
    'address',
    'is_verified',
    'meta',
])]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(VendorFactory::class)]
class Vendor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(VendorType::class, 'type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(VendorStatus::class, 'status_id');
    }

    public function preference(): MorphOne
    {
        return $this->morphOne(PanelPreference::class, 'account');
    }
}
