<?php

declare(strict_types=1);

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\VendorInventoryResource;
use App\Models\OrderItem;
use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
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
            'order_id' => $this->order_id,
            'vendor_inventory_id' => $this->vendor_inventory_id,
            'product_name_snapshot' => $this->product_name_snapshot,
            'unit_snapshot' => $this->unit_snapshot,
            'unit_price_snapshot' => MoneyService::money($this->unit_price_snapshot),
            'unit_price_snapshot_display' => MoneyService::displayTotalsFromUsd($this->unit_price_snapshot),
            'quantity' => $this->quantity,
            'subtotal' => MoneyService::money($this->subtotal),
            'subtotal_display' => MoneyService::displayTotalsFromUsd($this->subtotal),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'has_rated' => $this->relationLoaded('rating') && $this->rating !== null,
            'vendor_inventory' => new VendorInventoryResource($this->whenLoaded('vendorInventory')),
        ];
    }
}
