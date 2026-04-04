<?php

namespace App\Models;

use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @method static \Database\Factories\PaymentMethodFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod query()
 * @property int $payment_type_id
 * @property int $payment_status_id
 * @property string|null $billing_address
 * @property string|null $billing_city
 * @property string|null $billing_state
 * @property string|null $billing_zip_code
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereBillingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereBillingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereBillingZipCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereCardHolderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereCvv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereExpiryMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereExpiryYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod wherePaymentStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod wherePaymentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereUserId($value)
 * @mixin \Eloquent
 */
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
class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory;

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
