<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $order_status_id
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Order $order
 * @property-read OrderStatus $status
 */
#[Table('order_histories', key: 'id')]
#[Fillable([
    'order_id',
    'order_status_id',
    'notes',
])]

class OrderHistory extends Model
{
    use SoftDeletes;

    /**
     * Get the order this history belongs to.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get the order status for this history.
     *
     * @return BelongsTo<OrderStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id', 'id');
    }
}
