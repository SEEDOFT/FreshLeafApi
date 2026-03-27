<?php

namespace App\Models;

use Database\Factories\OrderTypeFactory;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 *
 * @method static \Database\Factories\OrderTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class OrderType extends Model
{
    /** @use HasFactory<OrderTypeFactory> */
    use HasFactory;

    /**
     * Get the orders for the type.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
