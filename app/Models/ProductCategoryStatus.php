<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductCategoryStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 */
#[Table('product_category_statuses', key: 'id', keyType: 'int')]
#[Fillable(['code', 'name'])]
#[UseFactory(ProductCategoryStatusFactory::class)]
class ProductCategoryStatus extends Model
{
    /** @use HasFactory<ProductCategoryStatusFactory> */
    use HasFactory;

    public const int ACTIVE = 1;

    public const int INACTIVE = 2;

    /**
     * Get the categories for the status.
     *
     * @return HasMany<ProductCategory, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'product_category_status_id', 'id');
    }
}
