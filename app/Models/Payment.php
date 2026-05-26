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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Override;

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
 * @property-read Collection<int, PaymentHistory> $histories
 * @property-read int|null $histories_count
 * @property Carbon|null $deleted_at
 */
#[Table('payments', key: 'id')]
#[Fillable([
    'order_id',
    'payment_method_id',
    'payment_number',
    'type_id',
    'status_id',
    'amount',
    'transaction_id',
    'paid_at',
])]
#[UseFactory(PaymentFactory::class)]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = self::generatePaymentNumber();
            }
        });
    }

    /**
     * Generate a sequential payment number like PAYDDMMYY000001
     */
    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY'.Carbon::now()->format('dmy');

        /** @var Payment|null $lastPayment */
        $lastPayment = self::query()
            ->where('payment_number', 'LIKE', $prefix.'%')
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            $sequence = (int) substr($lastPayment->payment_number, strlen($prefix));
            $nextSequence = $sequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix.str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
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
        return $this->belongsTo(PaymentType::class, 'type_id', 'id');
    }

    /**
     * Get the payment status.
     *
     * @return BelongsTo<PaymentStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class, 'status_id', 'id');
    }

    /**
     * Get the histories for the payment.
     *
     * @return HasMany<PaymentHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class, 'payment_id', 'id');
    }

    /**
     * Get the payment method used for this payment.
     *
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }
}
