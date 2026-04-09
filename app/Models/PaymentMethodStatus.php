<?php

namespace App\Models;

use Database\Factories\PaymentMethodStatusFactory;
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
 * @property-read Collection<int, UserPaymentMethod> $paymentMethods
 * @property-read int|null $paymentMethods_count
 *
 * @method static \Database\Factories\PaymentMethodStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethodStatus whereUpdatedAt($value)
 *
 * @property-read int|null $payment_methods_count
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class PaymentMethodStatus extends Model
{
    /** @use HasFactory<PaymentMethodStatusFactory> */
    use HasFactory;

    public const ACTIVE = 1;

    public const INACTIVE = 2;

    public const DELETE = 3;

    /**
     * Get the payment methods for the status.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(UserPaymentMethod::class);
    }
}
