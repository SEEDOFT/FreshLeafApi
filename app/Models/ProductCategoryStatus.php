<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductCategoryStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 */
class ProductCategoryStatus extends Model
{
    /** @use HasFactory<ProductCategoryStatusFactory> */
    use HasFactory;

    protected $fillable = ['code', 'name'];

    public const int ACTIVE = 1;

    public const int INACTIVE = 2;

    /**
     * @return HasMany<ProductCategory, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'product_category_status_id');
    }
}
