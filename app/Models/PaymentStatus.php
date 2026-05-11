<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
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
 */
#[Table('payment_statuses', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(PaymentStatusFactory::class)]
class PaymentStatus extends Model
{
    /** @use HasFactory<PaymentStatusFactory> */
    use HasFactory;

    public const int ACTIVE = 1;

    public const int INACTIVE = 2;

    public const int DELETE = 3;

    /**
     * Get the orders for the payment status.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_status_id', 'id');
    }

    /**
     * Get the payments for the status.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_status_id', 'id');
    }
}
