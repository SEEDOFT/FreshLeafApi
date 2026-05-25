<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

use function str_pad;
use function strlen;
use function substr;

/**
 * @property int $id
 * @property int $user_id
 * @property int $address_id
 * @property int $order_type_id
 * @property int $order_status_id
 * @property int $payment_status_id
 * @property string $order_number
 * @property Carbon $delivery_date
 * @property string $delivery_slot
 * @property numeric $subtotal
 * @property numeric $discount_amount
 * @property numeric $delivery_fee
 * @property numeric $tax_amount
 * @property numeric $total_amount
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Address $address
 * @property-read Collection<int, OrderItem> $items
 * @property-read int|null $items_count
 * @property-read PaymentStatus $paymentStatus
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read OrderStatus $status
 * @property-read int|null $status_histories_count
 * @property-read OrderType $type
 * @property-read User|null $user
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
#[Table('orders', key: 'id')]
#[Fillable([
    'user_id',
    'address_id',
    'order_type_id',
    'order_status_id',
    'payment_status_id',
    'order_number',
    'delivery_date',
    'delivery_slot',
    'subtotal',
    'discount_amount',
    'delivery_fee',
    'tax_amount',
    'total_amount',
    'notes',
])]
#[UseFactory(OrderFactory::class)]
class Order extends Model
{
    use SoftDeletes;

    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate a sequential order number like FLDDMMYY000001
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'FL'.Carbon::now()->format('dmy');

        /** @var Order|null $lastOrder */
        $lastOrder = self::query()
            ->where('order_number', 'LIKE', $prefix.'%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $sequence = (int) substr($lastOrder->order_number, strlen($prefix));
            $nextSequence = $sequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix.str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
        ];
    }

    /**
     * Get the user that owns the order.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the address for the order.
     *
     * @return BelongsTo<Address, $this>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    /**
     * Get the order type.
     *
     * @return BelongsTo<OrderType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(OrderType::class, 'order_type_id', 'id');
    }

    /**
     * Get the order status.
     *
     * @return BelongsTo<OrderStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id', 'id');
    }

    /**
     * Get the payment status.
     *
     * @return BelongsTo<PaymentStatus, $this>
     */
    public function paymentStatus(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id', 'id');
    }

    /**
     * Get the items for the order.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    /**
     * Get the payments for the order.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id', 'id');
    }
}
