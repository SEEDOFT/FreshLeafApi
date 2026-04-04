<?php

namespace App\Models;

use Database\Factories\ProductSubstitutionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $substitute_product_id
 * @property int $priority
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 * @property-read Product|null $substituteProduct
 * @method static \Database\Factories\ProductSubstitutionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution whereSubstituteProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSubstitution whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['product_id', 'substitute_product_id', 'priority', 'reason'])]
class ProductSubstitution extends Model
{
    /** @use HasFactory<ProductSubstitutionFactory> */
    use HasFactory;

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the substitute product.
     */
    public function substituteProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'substitute_product_id');
    }
}
