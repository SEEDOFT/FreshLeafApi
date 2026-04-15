<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PriceHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
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
 */
#[Table('price_histories', key: 'id')]
#[Fillable([
    'product_id',
    'product_variant_id',
    'old_price',
    'new_price',
    'changed_by',
    'changed_at',
])]
#[UseFactory(PriceHistoryFactory::class)]
class PriceHistory extends Model
{
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
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the product variant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }

    /**
     * Get the user who changed the price.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
    }
}
