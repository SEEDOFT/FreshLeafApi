<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
 * @property-read Collection<int, PriceHistory> $priceHistories
 * @property-read Collection<int, InventoryBatch> $inventoryBatches
 * @property-read Collection<int, InventoryMovement> $inventoryMovements
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read Collection<int, CartItem> $cartItems
 * @property-read Collection<int, PurchaseOrderItem> $purchaseOrderItems
 * @property-read Collection<int, AiRecommendationItem> $aiRecommendationItems
 * @property-read Collection<int, UserBehaviorEvent> $userBehaviorEvents
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product byCategory(int|Category $category)
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
 * @property-read int|null $ai_recommendation_items_count
 * @property-read int|null $cart_items_count
 * @property-read int|null $inventory_batches_count
 * @property-read int|null $inventory_movements_count
 * @property-read int|null $order_items_count
 * @property-read int|null $price_histories_count
 * @property-read int|null $purchase_order_items_count
 * @property-read Collection<int, ProductSubstitution> $substitutionsFor
 * @property-read int|null $substitutions_for_count
 * @property-read int|null $user_behavior_events_count
 * @mixin \Eloquent
 */
#[Fillable([
    'category_id',
    'product_type_id',
    'default_unit_id',
    'product_status_id',
    'name',
    'slug',
    'description',
    'nutrition_data',
    'shelf_life_days',
])]
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
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // Relationships

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

    /**
     * Get the substitutions where this product is the substitute.
     */
    public function substitutionsFor(): HasMany
    {
        return $this->hasMany(ProductSubstitution::class, 'substitute_product_id');
    }

    /**
     * Get the price histories for the product.
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Get the inventory batches for the product.
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    /**
     * Get the inventory movements for the product.
     */
    public function inventoryMovements(): HasManyThrough
    {
        return $this->hasManyThrough(InventoryMovement::class, InventoryBatch::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the cart items for the product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the purchase order items for the product.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the AI recommendation items for the product.
     */
    public function aiRecommendationItems(): HasMany
    {
        return $this->hasMany(AiRecommendationItem::class);
    }

    /**
     * Get the user behavior events for the product.
     */
    public function userBehaviorEvents(): HasMany
    {
        return $this->hasMany(UserBehaviorEvent::class);
    }

    // Scopes

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('product_status_id', ProductStatus::ACTIVE);
    }

    /**
     * Scope a query to filter products by category.
     */
    public function scopeByCategory(Builder $query, int|Category $category): Builder
    {
        $categoryId = $category instanceof Category ? $category->id : $category;

        return $query->where('category_id', $categoryId);
    }
}
