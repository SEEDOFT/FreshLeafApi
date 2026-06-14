<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Http\Resources\Shared\CurrencyResource;
use App\Http\Resources\Shared\StatusResource;
use App\Http\Resources\Shared\TypeResource;
use App\Models\Currency;
use App\Models\PackagingType;
use App\Models\User;
use App\Models\VendorInventory;
use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Override;

use function array_map;
use function is_array;

/**
 * @mixin VendorInventory
 * @mixin User
 * @mixin PackagingType
 */
class VendorInventoryResource extends JsonResource
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Language', App::getLocale());
        $currencyId = $this->currency_id ?? Currency::USD_ID;
        $price = MoneyService::money($this->price);
        $discountedPrice = MoneyService::money($this->discounted_price);

        return [
            'id' => $this->id,
            'price' => $price,
            'price_display' => MoneyService::displayTotals($price, $currencyId),
            'discount_percentage' => $this->discount_percentage,
            'discounted_price' => $discountedPrice,
            'discounted_price_display' => MoneyService::displayTotals($discountedPrice, $currencyId),
            'stock_quantity' => (float) $this->stock_quantity,
            'harvest_date' => $this->harvest_date?->format('Y-m-d'),
            'harvest_date_human' => $this->harvest_date?->diffForHumans(),
            'farm_location' => $this->farm_location,
            'province_of_origin' => $this->province_of_origin,
            'certification_type' => $this->certification_type,
            'average_rating' => $this->whenLoaded(
                'ratings',
                fn () => round((float) $this->ratings->avg('rating'), 1),
                0.0,
            ),
            'ratings_count' => $this->whenLoaded('ratings', fn () => $this->ratings->count(), 0),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'packaging_type' => new TypeResource($this->whenLoaded('packagingType')),
            'shelf_life_days' => $this->shelf_life_days,
            'batch_images' => is_array($this->batch_images)
                ? array_values(array_filter(
                    array_map(fn (string $image): ?string => resolve_image_url($image), $this->batch_images),
                ))
                : [],
            'status' => new StatusResource($this->whenLoaded('status')),
            'unit' => $this->whenLoaded(
                'unit',
                fn () => [
                    'id' => $this->unit->id,
                    'name' => $locale === 'km'
                        ? $this->unit->name_km
                        : $this->unit->name_en,
                    'symbol' => $this->unit->symbol,
                ],
                null
            ),
            'vendor' => $this->whenLoaded(
                'vendor',
                fn () => [
                    'id' => $this->vendor->id,
                    'name' => $this->vendor->fullName,
                    'phone' => $this->vendor->vendorProfile->contact_phone ?? $this->vendor->phone_number,
                    'email' => $this->vendor->email,
                    'address' => $this->vendor->vendorProfile->address ?? null,
                    'business_name' => $this->vendor->vendorProfile->business_name ?? null,
                    'shop_description' => $this->vendor->vendorProfile->shop_description ?? null,
                    'store_front_image' => resolve_image_url($this->vendor->vendorProfile->store_front_image),
                    'province' => $this->vendor->vendorProfile->province ?? null,
                    'opening_time' => $this->vendor->vendorProfile->opening_time ?? null,
                    'closing_time' => $this->vendor->vendorProfile->closing_time ?? null,
                    'is_open' => (bool) ($this->vendor->vendorProfile->is_open ?? false),
                    'is_verified' => (bool) ($this->vendor->vendorProfile->is_verified ?? false),
                    'product_count' => $this->vendor->active_inventories_count ??
                        $this->vendor->vendorInventories()->active()->count(),
                ],
                null
            ),
            'product' => $this->whenLoaded(
                'product',
                fn () => new ProductResource($this->product),
                null
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
