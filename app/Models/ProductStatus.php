<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $translated_name
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 */
#[Table('product_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(ProductStatusFactory::class)]
class ProductStatus extends Model
{
    /** @use HasFactory<ProductStatusFactory> */
    use HasFactory;

    public const int DRAFT_ID = 1;

    public const int PUBLISHED_ID = 2;

    public const int ARCHIVED_ID = 3;

    /**
     * Get the translated name attribute.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return 'name_'.App::getLocale();
    }

    /**
     * Get the products for the product status.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_status_id', 'id');
    }
}
