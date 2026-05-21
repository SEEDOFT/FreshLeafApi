<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $product_category_id
 * @property int $product_type_id
 * @property int $default_unit_id
 * @property int $product_status_id
 * @property string $name_en
 * @property string $name_km
 * @property string|null $translated_name
 * @property string $slug
 * @property string|null $description_en
 * @property string|null $description_km
 * @property string|null $translated_description
 * @property array<array-key, mixed>|null $nutrition_data
 * @property string|null $image_url
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductCategory $productCategory
 * @property-read Unit $defaultUnit
 * @property-read ProductStatus $status
 *
 * @method static Builder|Product active()
 * @method static Builder|Product byCategory(int|ProductCategory $category)
 */
#[Table('products', key: 'id', keyType: 'int')]
#[Fillable([
    'product_category_id',
    'product_type_id',
    'default_unit_id',
    'product_status_id',
    'name_en',
    'name_km',
    'slug',
    'description_en',
    'description_km',
    'nutrition_data',
    'image_url',
])]
#[UseFactory(ProductFactory::class)]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nutrition_data' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the localized name of the product.
     */
    public string $localizedName {
        get => App::getLocale() === 'km' ? ($this->name_km ?? $this->name_en) : $this->name_en;
    }

    /**
     * Get the localized description of the product.
     */
    public ?string $localizedDescription {
        get => App::getLocale() === 'km' ? ($this->description_km ?? $this->description_en) : $this->description_en;
    }

    /**
     * Get the translated name of the product.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::getLocale()};
    }

    /**
     * Get the translated description of the product.
     */
    public function getTranslatedDescriptionAttribute(): ?string
    {
        return $this->{'description_'.App::getLocale()};
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(static function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name_en);
            }
            if (empty($product->product_type_id)) {
                $product->product_type_id = 1;
            }
        });
    }

    /**
     * Get the category that owns the product (Alias for productCategory).
     *
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->productCategory();
    }

    /**
     * Get the product category that owns the product.
     *
     * @return BelongsTo<ProductCategory, $this>
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    /**
     * Get the product type.
     *
     * @return BelongsTo<ProductType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id', 'id');
    }

    /**
     * Get the default unit for the product.
     *
     * @return BelongsTo<Unit, $this>
     */
    public function defaultUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'default_unit_id', 'id');
    }

    /**
     * Get the product status.
     *
     * @return BelongsTo<ProductStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ProductStatus::class, 'product_status_id', 'id');
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('product_status_id', ProductStatus::PUBLISHED_ID);
    }

    /**
     * Scope a query to filter products by product category.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function byCategory(Builder $query, int|ProductCategory $category): void
    {
        $categoryId = $category instanceof ProductCategory
            ? $category->id : $category;

        $query->where('product_category_id', $categoryId);
    }
}
