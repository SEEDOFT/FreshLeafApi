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
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $product_category_id
 * @property int $product_type_id
 * @property int $default_unit_id
 * @property int $product_status_id
 * @property int|null $vendor_user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array<array-key, mixed>|null $nutrition_data
 * @property int|null $shelf_life_days
 * @property bool $is_organic
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductCategory $category
 * @property-read ProductCategory $productCategory
 * @property-read Unit $defaultUnit
 * @property-read ProductStatus $status
 * @property-read User|null $vendor
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
 * @property int $category_id
 */
#[Table('products', key: 'id')]
#[Fillable([
    'product_category_id',
    'product_type_id',
    'default_unit_id',
    'product_status_id',
    'vendor_user_id',
    'name',
    'slug',
    'description',
    'nutrition_data',
    'shelf_life_days',
    'is_organic',
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

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
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
        return $this->belongsTo(User::class, 'vendor_user_id', 'id');
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
     * Get the substitutions for the product.
     *
     * @return HasMany<ProductSubstitution, $this>
     */
    public function substitutions(): HasMany
    {
        return $this->hasMany(ProductSubstitution::class, 'product_id', 'id');
    }

    /**
     * Get the substitutions where this product is the substitute.
     *
     * @return HasMany<ProductSubstitution, $this>
     */
    public function substitutionsFor(): HasMany
    {
        return $this->hasMany(ProductSubstitution::class, 'substitute_product_id', 'id');
    }

    /**
     * Get the price histories for the product.
     *
     * @return HasMany<PriceHistory, $this>
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'product_id', 'id');
    }

    /**
     * Get the inventory batches for the product.
     *
     * @return HasMany<InventoryBatch, $this>
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class, 'product_id', 'id');
    }

    /**
     * Get the inventory movements for the product.
     *
     * @return HasManyThrough<InventoryMovement, InventoryBatch, $this>
     */
    public function inventoryMovements(): HasManyThrough
    {
        return $this->hasManyThrough(
            InventoryMovement::class,
            InventoryBatch::class,
            'product_id',
            'inventory_batch_id',
            'id',
            'id'
        );
    }

    /**
     * Get the order items for the product.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'id');
    }

    /**
     * Get the cart items for the product.
     *
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_id', 'id');
    }

    /**
     * Get the purchase order items for the product.
     *
     * @return HasMany<PurchaseOrderItem, $this>
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'product_id', 'id');
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('product_status_id', ProductStatus::ACTIVE);
    }

    /**
     * Scope a query to filter products by category.
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
     * Scope a query by vendor owner.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function byVendor(Builder $query, int $vendorUserId): void
    {
        $query->where('vendor_user_id', $vendorUserId);
    }
}
