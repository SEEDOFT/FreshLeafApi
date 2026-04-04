<?php

namespace App\Models;

use Database\Factories\OrderStatusFactory;
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
 * @property int $sort_order
 * @property string|null $color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @method static \Database\Factories\OrderStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name', 'sort_order', 'color'])]
class OrderStatus extends Model
{
    /** @use HasFactory<OrderStatusFactory> */
    use HasFactory;

    /**
     * Get the orders for the status.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
