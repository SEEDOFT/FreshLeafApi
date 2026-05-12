<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrderStatusFactory;
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
 * @property int $sort_order
 * @property string|null $color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 */
#[Table('order_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km', 'sort_order', 'color'])]
#[UseFactory(OrderStatusFactory::class)]
class OrderStatus extends Model
{
    /** @use HasFactory<OrderStatusFactory> */
    use HasFactory;

    public const int PENDING_ID = 1;

    public const int CONFIRMED_ID = 2;

    public const int PREPARING_ID = 3;

    public const int DELIVERED_ID = 4;

    public const int CANCELLED_ID = 5;

    public const string PENDING = 'PENDING';

    public const string CONFIRMED = 'CONFIRMED';

    public const string PREPARING = 'PREPARING';

    public const string DELIVERED = 'DELIVERED';

    public const string CANCELLED = 'CANCELLED';

    /**
     * Get the orders for the status.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'order_status_id', 'id');
    }
}
