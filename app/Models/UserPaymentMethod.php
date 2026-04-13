<?php

namespace App\Models;

use Database\Factories\UserPaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $payment_method_type_id
 * @property int $payment_method_status_id
 * @property string $label
 * @property string $card_holder_name
 * @property string $card_number
 * @property int $expiry_month
 * @property int $expiry_year
 * @property string $cvv
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read PaymentMethodType $type
 * @property-read PaymentMethodStatus $status
 */
#[Table('user_payment_methods', key: 'id')]
#[Fillable([
    'user_id',
    'payment_method_type_id',
    'payment_method_status_id',
    'label',
    'card_holder_name',
    'card_number',
    'expiry_month',
    'expiry_year',
    'cvv',
    'is_default',
    'billing_address',
    'billing_city',
    'billing_state',
    'billing_zip_code',
])]
#[UseFactory(UserPaymentMethodFactory::class)]
class UserPaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'card_holder_name' => 'encrypted',
            'card_number' => 'encrypted',
            'cvv' => 'encrypted',
            'expiry_month' => 'integer',
            'expiry_year' => 'integer',
            'is_default' => 'boolean',
            'billing_address' => 'string',
            'billing_city' => 'string',
            'billing_state' => 'string',
            'billing_zip_code' => 'string',
        ];
    }

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Get the user that owns the payment method.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment method type of the payment method.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PaymentMethodType::class, 'payment_method_type_id');
    }

    /**
     * Get the payment method status of the payment method.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PaymentMethodStatus::class, 'payment_method_status_id');
    }
}
