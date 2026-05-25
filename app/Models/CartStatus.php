<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CartStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
#[Table('cart_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(CartStatusFactory::class)]
class CartStatus extends Model
{
    use SoftDeletes;

    /** @use HasFactory<CartStatusFactory> */
    use HasFactory;

    public const int ACTIVE_ID = 1;

    public const int REMOVED_ID = 2;

    public const int CHECKED_OUT_ID = 3;

    public const string ACTIVE = 'ACTIVE';

    public const string REMOVED = 'REMOVED';

    public const string CHECKED_OUT = 'CHECKED_OUT';

    /**
     * Get the carts for the status.
     */
    /**
     * @return HasMany<Cart, $this>
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'cart_status_id', 'id');
    }
}
