<?php

declare(strict_types=1);

namespace App\Http\Resources\Cart;

use App\Http\Resources\Product\VendorInventoryResource;
use App\Models\Cart;
use App\Models\VendorInventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $inventory = $this->relationLoaded('vendorInventory') ? $this->vendorInventory : null;
        $price = $inventory instanceof VendorInventory ? $inventory->price : 0;
        $subtotal = (float) $this->quantity * (float) $price;

        return [
            'id' => $this->id,
            'vendor_inventory_id' => $this->vendor_inventory_id,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $price,
            'subtotal' => round($subtotal, 2),
            'status' => $this->whenLoaded('status', fn () => [
                'id' => $this->cart_status_id,
                'code' => $this->status->code,
                'name_en' => $this->status->name_en,
                'name_km' => $this->status->name_km,
            ]),
            'vendor_inventory' => $inventory instanceof VendorInventory
                ? new VendorInventoryResource($inventory)
                : null,
            'created_at' => \optional($this->created_at)->toIso8601String(),
            'updated_at' => \optional($this->updated_at)->toIso8601String(),
        ];
    }
}
