<?php

namespace App\Models;

use Database\Factories\PaymentStatusFactory;
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
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read Collection<int, PaymentMethod> $paymentMethods
 * @property-read int|null $paymentMethods_count
 *
 * @method static \Database\Factories\PaymentStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentStatus whereUpdatedAt($value)
 *
 * @property-read int|null $payment_methods_count
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class PaymentStatus extends Model
{
    /** @use HasFactory<PaymentStatusFactory> */
    use HasFactory;

    public const ACTIVE = 1;

    public const INACTIVE = 2;

    public const DELETE = 3;

    /**
     * Get the orders for the payment status.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the payments for the status.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the payment methods for the status.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
