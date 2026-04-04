<?php

namespace App\Models;

use Database\Factories\PaymentMethodTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PaymentMethod> $paymentMethods
 * @property-read int|null $paymentMethods_count
 * @method static \Database\Factories\PaymentMethodTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodType whereUpdatedAt($value)
 * @property-read int|null $payment_methods_count
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class PaymentMethodType extends Model
{
    /** @use HasFactory<PaymentMethodTypeFactory> */
    use HasFactory;

    public const VISA = 1;

    public const MASTER_CARD = 2;

    public const UNION_PAY = 3;

    public const AMERICAN_EXPRESS = 4;

    public const DISCOVER = 5;

    public const JCB = 6;

    public const DINERS_CLUB = 7;

    public const PAYPAL = 8;

    public const STRIPE = 9;

    /**
     * Get the payment methods for the type.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
