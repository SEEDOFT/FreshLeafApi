<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $product_category_id
 * @property int|null $organic_category_id
 * @property int $product_type_id
 * @property int $default_unit_id
 * @property int $product_status_id
 * @property int|null $user_id
 * @property string $name_en
 * @property string $name_km
 * @property string $slug
 * @property string|null $description_en
 * @property string|null $description_km
 * @property string|null $selling_unit
 * @property float|null $price_per_unit
 * @property float $available_stock
 * @property string|null $farm_name_location
 * @property string|null $farming_method
 * @property Carbon|null $harvest_date
 * @property bool $is_active
 * @property bool $is_organic
 * @property array<array-key, mixed>|null $nutrition_data
 * @property int|null $shelf_life_days
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductCategory $productCategory
 * @property-read Category|null $organicCategory
 * @property-read Unit $defaultUnit
 * @property-read ProductStatus $status
 * @property-read User|null $vendor
 * @property-read Collection<int, ProductVariant> $variants
 * @property-read ProductDiscount|null $activeDiscount
 *
 * @method static Builder|Product active()
 * @method static Builder|Product byCategory(int|ProductCategory $category)
 * @method static Builder|Product byOrganicCategory(int|Category $category)
 * @method static Builder|Product byVendor(int $userId)
 */
#[Table('products', key: 'id')]
#[Fillable([
    'product_category_id',
    'organic_category_id',
    'product_type_id',
    'default_unit_id',
    'product_status_id',
    'user_id',
    'name_en',
    'name_km',
    'slug',
    'description_en',
    'description_km',
    'selling_unit',
    'price_per_unit',
    'available_stock',
    'farm_name_location',
    'farming_method',
    'harvest_date',
    'is_active',
    'is_organic',
    'nutrition_data',
    'shelf_life_days',
])]
#[UseFactory(ProductFactory::class)]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nutrition_data' => 'array',
            'is_organic' => 'boolean',
            'is_active' => 'boolean',
            'harvest_date' => 'date',
            'deleted_at' => 'datetime',
            'price_per_unit' => 'float',
            'available_stock' => 'float',
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
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(static function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name_en);
            }
        });
    }

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo<Category, $this>
     */
    public function organicCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'organic_category_id', 'id');
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
     * Get the vendor owner for the product.
     *
     * @return BelongsTo<User, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the variants for the product.
     *
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    /**
     * Get the current active discount for the product.
     *
     * @return HasOne<ProductDiscount, $this>
     */
    public function activeDiscount(): HasOne
    {
        return $this->hasOne(ProductDiscount::class, 'product_id', 'id')
            ->where('is_active', true)
            ->where(static function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(static function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Get the current discount percentage.
     */
    public public(set) int $discountPercentage {
        get {
            $discount = $this->activeDiscount;

            return ($discount !== null) ? $discount->discount_percentage : 0;
        }
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)
            ->where('product_status_id', ProductStatus::ACTIVE);
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

    /**
     * Scope a query to filter products by organic category.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function byOrganicCategory(Builder $query, int|Category $category): void
    {
        $categoryId = $category instanceof Category
            ? $category->id : $category;

        $query->where('organic_category_id', $categoryId);
    }

    /**
     * Scope a query by vendor owner.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function byVendor(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }
}
