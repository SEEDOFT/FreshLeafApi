<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Http\Resources\User\CurrencyResource;
use App\Models\PackagingType;
use App\Models\User;
use App\Models\VendorInventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
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
        $locale = $request->header('Accept-Language', 'km');

        return [
            'id' => $this->id,
            'price' => (float) $this->price,
            'discount_percentage' => (float) $this->discount_percentage,
            'stock_quantity' => (float) $this->stock_quantity,
            'harvest_date' => $this->harvest_date?->format('Y-m-d'),
            'farm_location' => $this->farm_location,
            'province_of_origin' => $this->province_of_origin,
            'certification_type' => $this->certification_type,
            'currency' => $this->whenLoaded(
                'currency',
                fn () => new CurrencyResource($this->currency),
                null
            ),
            'packaging_type' => $this->whenLoaded(
                'packagingType',
                fn () => [
                    'id' => $this->packagingType?->id,
                    'name' => $locale === 'km'
                        ? $this->packagingType?->name_km
                        : $this->packagingType?->name_en,
                ],
                null
            ),
            'shelf_life_days' => $this->shelf_life_days,
            'batch_images' => is_array($this->batch_images)
                ? array_map(fn (string $image): string => Storage::disk('public')->url($image), $this->batch_images)
                : [],
            'status' => $this->whenLoaded(
                'status',
                fn () => [
                    'id' => $this->status->id,
                    'name' => $locale === 'km'
                        ? $this->status->name_km
                        : $this->status->name_en,
                ],
                null
            ),
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
                    'phone' => $this->vendor->vendorProfile->contact_phone,
                    'email' => $this->vendor->email,
                    'address' => $this->vendor->vendorProfile->address,
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
