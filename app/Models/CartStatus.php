<?php

namespace App\Models;

use Database\Factories\CartStatusFactory;
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
 * @property-read Collection<int, Cart> $carts
 * @property-read int|null $carts_count
 */
#[Table('cart_statuses', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(CartStatusFactory::class)]
class CartStatus extends Model
{
    use HasFactory;

    /**
     * Get the carts for the status.
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}
