<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property int $product_category_status_id
 * @property string $name_en
 * @property string|null $name_km
 * @property string|null $description_en
 * @property string|null $description_km
 * @property string|null $image_url
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read ProductCategoryStatus $status
 */
#[Table('product_categories')]
#[Fillable([
    'product_category_status_id',
    'name_en',
    'name_km',
    'description_en',
    'description_km',
    'image_url',
    'slug',
])]
class ProductCategory extends Model
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasFactory;

    public const int LEAFY = 1;
    public const int FRUIT = 2;
    public const int ROOT_AND_TUBER = 3;
    public const int BULB_AND_STEM = 4;
    public const int LEGUME = 5;
    public const int INDIGENOUS_AND_WILD = 6;

    /**
     * Get the localized name of the category.
     */
    public string $localizedName {
        get => App::getLocale() === 'km' ? ($this->name_km ?? $this->name_en) : $this->name_en;
    }

    /**
     * Get the localized description of the category.
     */
    public ?string $localizedDescription {
        get => App::getLocale() === 'km' ? ($this->description_km ?? $this->description_en) : $this->description_en;
    }

    /**
     * Get the status of the category.
     *
     * @return BelongsTo<ProductCategoryStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ProductCategoryStatus::class, 'product_category_status_id');
    }

    /**
     * Get the products for the category.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_category_id', 'id');
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active($query): void
    {
        $query->where('product_category_status_id', ProductCategoryStatus::ACTIVE);
    }
}
