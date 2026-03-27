<?php

namespace App\Models;

use Database\Factories\CartStatusFactory;
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
 * @property-read Collection<int, Cart> $carts
 * @property-read int|null $carts_count
 *
 * @method static \Database\Factories\CartStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartStatus whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class CartStatus extends Model
{
    /** @use HasFactory<CartStatusFactory> */
    use HasFactory;

    /**
     * Get the carts for the status.
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}
