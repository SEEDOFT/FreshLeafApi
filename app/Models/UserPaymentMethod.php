<?php

namespace App\Models;

use Database\Factories\UserPaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
 *
 * @method static \Database\Factories\UserPaymentMethodFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod query()
 *
 * @property int $payment_type_id
 * @property int $payment_status_id
 * @property string|null $billing_address
 * @property string|null $billing_city
 * @property string|null $billing_state
 * @property string|null $billing_zip_code
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereBillingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereBillingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereBillingZipCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereCardHolderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereCvv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereExpiryMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereExpiryYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod wherePaymentStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod wherePaymentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereUpdatedAt($value)
 *
 * @property Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod wherePaymentMethodStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod wherePaymentMethodTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPaymentMethod withoutTrashed()
 *
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
class UserPaymentMethod extends Model
{
    /** @use HasFactory<UserPaymentMethodFactory> */
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
