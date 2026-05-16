<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Models\User;
use App\Models\VendorInventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

use function translate;

/**
 * @mixin VendorInventory
 * @mixin User
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
            'packaging_type' => $this->packaging_type,
            'shelf_life_days' => $this->shelf_life_days,
            'batch_images' => $this->batch_images,
            'status' => $this->whenLoaded(
                'status',
                fn () => [
                    'id' => $this->inventory_status_id,
                    'name' => translate($this->status->name_en, $this->status->name_km),
                ]
            ),
            'unit' => $this->whenLoaded(
                'unit',
                fn () => [
                    'id' => $this->unit_id,
                    'name' => $this->unit->name,
                    'symbol' => $this->unit->symbol,
                ]),
            'vendor' => $this->whenLoaded(
                'vendor',
                fn () => [
                    'id' => $this->vendor_id,
                    'name' => $this->vendor->fullName,
                    'phone' => $this->vendor->phone_number,
                    'email' => $this->vendor->email,
                ]
            ),
            'product' => $this->whenLoaded(
                'product',
                fn () => new ProductResource($this->product)
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
