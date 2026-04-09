<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
 * @property-read UserAddress $address
 * @property-read Collection<int, OrderItem> $items
 * @property-read int|null $items_count
 * @property-read PaymentStatus $paymentStatus
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read OrderStatus $status
 * @property-read int|null $status_histories_count
 * @property-read OrderType $type
 * @property-read User|null $user
 *
 * @method static \Database\Factories\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliverySlot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 *
 * @mixin \Eloquent
 */
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
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
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
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the address for the order.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class);
    }

    /**
     * Get the order type.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(OrderType::class, 'order_type_id');
    }

    /**
     * Get the order status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    /**
     * Get the payment status.
     */
    public function paymentStatus(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id');
    }

    /**
     * Get the items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for the order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
