<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $discount_percentage
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read ExchangeRateHistory[] $histories
 */
#[Table('product_discounts', key: 'id')]
#[Fillable([
    'product_id',
    'discount_percentage',
    'starts_at',
    'ends_at',
    'is_active',
])]
class ProductDiscount extends Model
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
            'discount_percentage' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determine if the discount is currently active.
     */
    public public(set) bool $isValid {
        get {
            if (! $this->is_active) {
                return false;
            }

            $now = now();

            if ($this->starts_at && $this->starts_at->isFuture()) {
                return false;
            }

            if ($this->ends_at && $this->ends_at->isPast()) {
                return false;
            }

            return true;
        }
    }

    /**
     * Get the product that owns the discount.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the history of changes for this discount.
     *
     * @return HasMany<ProductDiscountHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ProductDiscountHistory::class, 'product_discount_id', 'id');
    }
}
