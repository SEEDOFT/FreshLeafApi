<?php

declare(strict_types=1);

namespace App\Http\Resources\Cart;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CartItem
 */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => (float) $this->subtotal,
            'status' => $this->whenLoaded('status', fn () => [
                'id' => $this->user_cart_item_status_id,
                'name' => $this->status->name,
            ]),
            'type' => $this->whenLoaded('type', fn () => [
                'id' => $this->user_cart_item_type_id,
                'name' => $this->type->name,
            ]),
            'vendor_inventory' => $this->whenLoaded('vendorInventory', fn () => [
                'id' => $this->vendorInventory->id,
                'price' => (float) $this->vendorInventory->price,
                'stock_quantity' => (float) $this->vendorInventory->stock_quantity,
                'unit' => $this->vendorInventory->relationLoaded('unit') ? [
                    'id' => $this->vendorInventory->unit->id ?? null,
                    'name' => $this->vendorInventory->unit->name ?? null,
                ] : null,
                'vendor' => $this->vendorInventory->relationLoaded('vendor') ? [
                    'id' => $this->vendorInventory->vendor->id ?? null,
                    'name' => trim("{$this->vendorInventory->vendor->first_name} {$this->vendorInventory->vendor->last_name}"),
                ] : null,
                'product' => $this->vendorInventory->relationLoaded('product') ? [
                    'id' => $this->vendorInventory->product->id ?? null,
                    'name_en' => $this->vendorInventory->product->name_en ?? null,
                    'name_km' => $this->vendorInventory->product->name_km ?? null,
                    'image_url' => $this->vendorInventory->product->image_url ?? null,
                ] : null,
            ]),
            'created_at' => \optional($this->created_at)->toIso8601String(),
            'updated_at' => \optional($this->updated_at)->toIso8601String(),
        ];
    }
}
