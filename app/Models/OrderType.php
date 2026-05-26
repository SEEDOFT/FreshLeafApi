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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property-read string $name
 * @property-read string|null $translated_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property Carbon|null $deleted_at
 */
#[Table('order_types', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(OrderTypeFactory::class)]
class OrderType extends Model
{
    /** @use HasFactory<OrderTypeFactory> */
    use HasFactory, SoftDeletes;

    public const int STANDARD_ID = 1;

    /**
     * Get the translated name of the type.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::getLocale()};
    }

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
