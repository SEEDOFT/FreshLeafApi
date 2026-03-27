<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $category_id
 * @property int $product_type_id
 * @property int $default_unit_id
 * @property int $product_status_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array<array-key, mixed>|null $nutrition_data
 * @property int|null $shelf_life_days
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category $category
 * @property-read Unit $defaultUnit
 * @property-read ProductStatus $status
 * @property-read Collection<int, ProductSubstitution> $substitutions
 * @property-read int|null $substitutions_count
 * @property-read ProductType $type
 * @property-read Collection<int, ProductVariant> $variants
 * @property-read int|null $variants_count
 *
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDefaultUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereNutritionData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShelfLifeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[Fillable(['category_id', 'product_type_id', 'default_unit_id', 'product_status_id', 'name', 'slug', 'description', 'nutrition_data', 'shelf_life_days'])]
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
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the product type.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    /**
     * Get the default unit for the product.
     */
    public function defaultUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'default_unit_id');
    }

    /**
     * Get the product status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ProductStatus::class, 'product_status_id');
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the substitutions for the product.
     */
    public function substitutions(): HasMany
    {
        return $this->hasMany(ProductSubstitution::class, 'product_id');
    }
}
