<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentFactory;
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
 * @property int $order_id
 * @property int $payment_type_id
 * @property int $payment_status_id
 * @property numeric $amount
 * @property string|null $transaction_reference
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read PaymentStatus $status
 * @property-read PaymentType $type
 * @property Carbon|null $deleted_at
 */
#[Table('payments', key: 'id')]
#[Fillable([
    'order_id',
    'payment_type_id',
    'payment_status_id',
    'amount',
    'transaction_reference',
    'paid_at',
])]
#[UseFactory(PaymentFactory::class)]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the order that owns the payment.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get the payment type.
     *
     * @return BelongsTo<PaymentType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id', 'id');
    }

    /**
     * Get the payment status.
     *
     * @return BelongsTo<PaymentStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id', 'id');
    }
}
