<?php

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
#[Table('order_statuses', key: 'id')]
#[Fillable(['name', 'sort_order', 'color'])]
#[UseFactory(OrderStatusFactory::class)]
class OrderStatus extends Model
{
    use HasFactory;

    /**
     * Get the orders for the status.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
