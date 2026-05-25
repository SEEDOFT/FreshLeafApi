<?php

declare(strict_types=1);

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\VendorInventoryResource;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'vendor_inventory_id' => $this->vendor_inventory_id,
            'product_name_snapshot' => $this->product_name_snapshot,
            'unit_snapshot' => $this->unit_snapshot,
            'unit_price_snapshot' => $this->unit_price_snapshot,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'vendor_inventory' => new VendorInventoryResource($this->whenLoaded('vendorInventory')),
        ];
    }
}
