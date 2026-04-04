<?php

namespace App\Models;

use Database\Factories\OrderStatusHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $from_order_status_id
 * @property int $to_order_status_id
 * @property int $changed_by
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $changer
 * @property-read OrderStatus $fromStatus
 * @property-read Order $order
 * @property-read OrderStatus $toStatus
 * @method static \Database\Factories\OrderStatusHistoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory whereFromOrderStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory whereToOrderStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['order_id', 'from_order_status_id', 'to_order_status_id', 'changed_by', 'note'])]
class OrderStatusHistory extends Model
{
    /** @use HasFactory<OrderStatusHistoryFactory> */
    use HasFactory;

    /**
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the status the order was changed from.
     */
    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'from_order_status_id');
    }

    /**
     * Get the status the order was changed to.
     */
    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'to_order_status_id');
    }

    /**
     * Get the user who changed the status.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
