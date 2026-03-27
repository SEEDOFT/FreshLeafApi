<?php

namespace App\Models;

use Database\Factories\ProductStatusFactory;
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
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 *
 * @method static \Database\Factories\ProductStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductStatus whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class ProductStatus extends Model
{
    /** @use HasFactory<ProductStatusFactory> */
    use HasFactory;

    /**
     * Get the products for the product status.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
