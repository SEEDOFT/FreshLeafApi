<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductSubstitutionFactory;
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
 * @property int $substitute_product_id
 * @property int $priority
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 * @property-read Product|null $substituteProduct
 */
#[Table('product_substitutions', key: 'id')]
#[Fillable(['product_id', 'substitute_product_id', 'priority', 'reason'])]
#[UseFactory(ProductSubstitutionFactory::class)]
class ProductSubstitution extends Model
{
    /** @use HasFactory<ProductSubstitutionFactory> */
    use HasFactory;

    /**
     * Get the product.
     */
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the substitute product.
     */
    /**
     * @return BelongsTo<Product, $this>
     */
    public function substituteProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'substitute_product_id', 'id');
    }
}
