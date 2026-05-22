<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Models\PackagingType;
use App\Models\User;
use App\Models\VendorInventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

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
        return [
            'id' => $this->id,
            'price' => (float) $this->price,
            'stock_quantity' => (float) $this->stock_quantity,
            'harvest_date' => $this->harvest_date?->format('Y-m-d'),
            'farm_location' => $this->farm_location,
            'province_of_origin' => $this->province_of_origin,
            'certification_type' => $this->certification_type,
            'packaging_type' => $this->whenLoaded(
                'packagingType',
                fn () => [
                    'id' => $this->packagingType?->id,
                    'name' => $this->packagingType?->translated_name,
                ],
                null
            ),
            'shelf_life_days' => $this->shelf_life_days,
            'batch_images' => $this->batch_images,
            'status' => $this->whenLoaded(
                'status',
                fn () => [
                    'id' => $this->status->id,
                    'name' => $this->status->translated_name,
                ],
                null
            ),
            'unit' => $this->whenLoaded(
                'unit',
                fn () => [
                    'id' => $this->unit->id,
                    'name' => $this->unit->translated_name,
                    'symbol' => $this->unit->symbol,
                ],
                null
            ),
            'vendor' => $this->whenLoaded(
                'vendor',
                fn () => [
                    'id' => $this->vendor->id,
                    'name' => $this->vendor->fullName,
                    'phone' => $this->vendor->phone_number,
                    'email' => $this->vendor->email,
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
