<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrderTypeFactory;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 */
#[Table('order_types', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(OrderTypeFactory::class)]
class OrderType extends Model
{
    /** @use HasFactory<OrderTypeFactory> */
    use HasFactory;

    /**
     * Get the orders for the type.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'order_type_id', 'id');
    }
}
