<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\MoneyService;
use Database\Factories\VendorInventoryFactory;
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
use Illuminate\Support\Facades\DB;
use Override;

/**
 * @property int $id
 * @property int $vendor_id
 * @property int $product_id
 * @property int $currency_id
 * @property int $inventory_status_id
 * @property string $price
 * @property string $stock_quantity
 * @property int $unit_id
 * @property Carbon|null $harvest_date
 * @property string|null $farm_location
 * @property string|null $province_of_origin
 * @property string|null $certification_type
 * @property int|null $packaging_type_id
 * @property int|null $shelf_life_days
 * @property array<string>|null $batch_images
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $discounted_price
 * @property-read User $vendor
 * @property-read Product $product
 * @property-read PackagingType|null $packagingType
 * @property-read Currency $currency
 * @property-read Unit $unit
 * @property-read VendorInventoryStatus $status
 * @property-read Collection<int, VendorInventoryHistory> $histories
 * @property-read int|null $histories_count
 * @property-read VendorInventoryDiscount|null $activeDiscount
 * @property-read Collection<int, VendorInventoryDiscount> $discounts
 * @property-read string $discount_percentage
 * @property-read Collection<int, VendorInventoryRating> $ratings
 * @property-read float $average_rating
 * @property-read int $ratings_count
 */
#[Table('vendor_inventories', key: 'id', keyType: 'int')]
#[Fillable([
    'vendor_id',
    'product_id',
    'currency_id',
    'inventory_status_id',
    'price',
    'stock_quantity',
    'unit_id',
    'harvest_date',
    'farm_location',
    'province_of_origin',
    'certification_type',
    'packaging_type_id',
    'shelf_life_days',
    'batch_images',
    'expiring_alert_sent_at',
])]
#[UseFactory(VendorInventoryFactory::class)]
class VendorInventory extends Model
{
    /** @use HasFactory<VendorInventoryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'decimal:2',
            'harvest_date' => 'date',
            'batch_images' => 'array',
            'expiring_alert_sent_at' => 'datetime',
        ];
    }

    /**
     * Get the discounted price based on the current price and active discount percentage.
     */
    public function getDiscountedPriceAttribute(): string
    {
        return MoneyService::discountUnitPrice($this->price, $this->discount_percentage);
    }

    /**
     * Get the active discount percentage.
     */
    public function getDiscountPercentageAttribute(): string
    {
        $activeDiscount = $this->activeDiscount;

        if (! $activeDiscount) {
            return '0.00';
        }

        if (MoneyService::compare($activeDiscount->discount_percentage, '0') < 0) {
            return '0.00';
        }

        if (MoneyService::compare($activeDiscount->discount_percentage, '100') > 0) {
            return '100.00';
        }

        return MoneyService::money($activeDiscount->discount_percentage);
    }

    /**
     * Get the vendor that owns the inventory.
     *
     * @return BelongsTo<User, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id', 'id')
            ->where('users.user_type_id', UserType::VENDOR_ID);
    }

    /**
     * Get the master product from the catalog.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the currency associated with this inventory.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    /**
     * Get the unit associated with this inventory.
     *
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    /**
     * Get the packaging type associated with this inventory.
     *
     * @return BelongsTo<PackagingType, $this>
     */
    public function packagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class, 'packaging_type_id', 'id');
    }

    /**
     * Get the status associated with this inventory.
     *
     * @return BelongsTo<VendorInventoryStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(
            VendorInventoryStatus::class,
            'inventory_status_id',
            'id',
        );
    }

    /**
     * Get the adjustments for this inventory.
     *
     * @return HasMany<InventoryAdjustment, $this>
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class, 'vendor_inventory_id', 'id');
    }

    /**
     * Get all discounts for this inventory.
     *
     * @return HasMany<VendorInventoryDiscount, $this>
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(VendorInventoryDiscount::class, 'vendor_inventory_id', 'id');
    }

    /**
     * Get the currently active discount.
     *
     * @return HasOne<VendorInventoryDiscount, $this>
     */
    public function activeDiscount()
    {
        return $this->hasOne(VendorInventoryDiscount::class, 'vendor_inventory_id', 'id')
            ->where(function (Builder $query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', Carbon::now()->format('Y-m-d H:i:s'));
            })
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', Carbon::now()->format('Y-m-d H:i:s'));
            })
            ->latest('id');
    }

    /**
     * Adjust the stock quantity and log it.
     */
    public function adjustStock(
        float $change,
        string $type,
        ?string $reason = null,
        ?string $proofImagePath = null,
        ?string $notes = null,
    ): void {
        DB::transaction(function () use (
            $change,
            $type,
            $reason,
            $proofImagePath,
            $notes,
        ) {
            $this->increment('stock_quantity', $change);

            $this->adjustments()->create([
                'user_id' => auth()->id(),
                'quantity_change' => $change,
                'type' => $type,
                'reason' => $reason,
                'proof_image_path' => $proofImagePath,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Scope a query to only include active inventory items.
     *
     * @param  Builder<VendorInventory>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('inventory_status_id', VendorInventoryStatus::AVAILABLE_ID);
    }

    /**
     * Get the histories for the vendor inventory.
     *
     * @return HasMany<VendorInventoryHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(VendorInventoryHistory::class, 'vendor_inventory_id', 'id');
    }

    /**
     * @return HasMany<VendorInventoryRating, $this>
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(VendorInventoryRating::class, 'vendor_inventory_id', 'id');
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->ratings->avg('rating') ?? 0.0, 1);
    }

    public function getRatingsCountAttribute(): int
    {
        return $this->ratings->count();
    }
}
