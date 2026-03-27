<?php

namespace App\Models;

use Database\Factories\PriceHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $product_variant_id
 * @property numeric $old_price
 * @property numeric $new_price
 * @property int $changed_by
 * @property Carbon $changed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $changer
 * @property-read Product|null $product
 * @property-read ProductVariant $variant
 *
 * @method static \Database\Factories\PriceHistoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereNewPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereOldPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['product_id', 'product_variant_id', 'old_price', 'new_price', 'changed_by', 'changed_at'])]
class PriceHistory extends Model
{
    /** @use HasFactory<PriceHistoryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get the user who changed the price.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
