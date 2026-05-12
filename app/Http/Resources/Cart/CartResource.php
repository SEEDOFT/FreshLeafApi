<?php

declare(strict_types=1);

namespace App\Http\Resources\Cart;

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
            'vendor_inventory' => $inventory instanceof VendorInventory ? [
                'id' => $inventory->id,
                'price' => (float) $inventory->price,
                'stock_quantity' => (float) $inventory->stock_quantity,
                'unit' => $inventory->relationLoaded('unit') ? [
                    'id' => $inventory->unit->id ?? null,
                    'name_en' => $inventory->unit->name_en ?? null,
                    'name_km' => $inventory->unit->name_km ?? null,
                    'symbol' => $inventory->unit->symbol ?? null,
                ] : null,
                'vendor' => $inventory->relationLoaded('vendor') ? [
                    'id' => $inventory->vendor->id ?? null,
                    'name' => trim("{$inventory->vendor->first_name} {$inventory->vendor->last_name}"),
                ] : null,
                'product' => $inventory->relationLoaded('product') ? [
                    'id' => $inventory->product->id ?? null,
                    'name_en' => $inventory->product->name_en ?? null,
                    'name_km' => $inventory->product->name_km ?? null,
                    'image_url' => $inventory->product->image_url ?? null,
                ] : null,
            ] : null,
            'created_at' => \optional($this->created_at)->toIso8601String(),
            'updated_at' => \optional($this->updated_at)->toIso8601String(),
        ];
    }
}
