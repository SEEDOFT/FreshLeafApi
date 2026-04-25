<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_discount_id
 * @property int $old_percentage
 * @property int $new_percentage
 * @property int $changed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductDiscount $productDiscount
 * @property-read User $changer
 */
#[Table('product_discount_histories', key: 'id')]
#[Fillable([
    'product_discount_id',
    'old_percentage',
    'new_percentage',
    'changed_by',
])]
class ProductDiscountHistory extends Model
{
    use HasFactory;

    /**
     * Get the discount record.
     *
     * @return BelongsTo<ProductDiscount, $this>
     */
    public function productDiscount(): BelongsTo
    {
        return $this->belongsTo(ProductDiscount::class, 'product_discount_id', 'id');
    }

    /**
     * Get the user who changed the discount.
     *
     * @return BelongsTo<User, $this>
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
    }
}
