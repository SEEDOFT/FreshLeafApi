<?php

declare(strict_types=1);

namespace App\Http\Resources\Cart;

use App\Http\Resources\Product\VendorInventoryResource;
use App\Http\Resources\Shared\StatusResource;
use App\Models\Cart;
use App\Models\CartStatus;
use App\Models\Currency;
use App\Models\VendorInventory;
use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Cart
 * @mixin CartStatus
 */
class CartResource extends JsonResource
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $inventory = $this->relationLoaded('vendorInventory') ? $this->vendorInventory : null;
        $currencyId = $inventory instanceof VendorInventory
            ? ($inventory->currency_id ?? Currency::USD_ID)
            : Currency::USD_ID;
        $price = $inventory instanceof VendorInventory ? MoneyService::money($inventory->price) : '0.00';
        $discountedPrice = $inventory instanceof VendorInventory
            ? MoneyService::money($inventory->discounted_price)
            : '0.00';
        $subtotal = MoneyService::mul((string) $this->quantity, $discountedPrice);

        return [
            'id' => $this->id,
            'vendor_inventory_id' => $this->vendor_inventory_id,
            'quantity' => (float) $this->quantity,
            'unit_price' => $price,
            'unit_price_display' => MoneyService::displayTotals($price, $currencyId),
            'discounted_unit_price' => $discountedPrice,
            'discounted_unit_price_display' => MoneyService::displayTotals($discountedPrice, $currencyId),
            'subtotal' => $subtotal,
            'subtotal_display' => MoneyService::displayTotals($subtotal, $currencyId),
            'status' => new StatusResource($this->whenLoaded('status')),
            'vendor_inventory' => $inventory instanceof VendorInventory
                ? new VendorInventoryResource($inventory)
                : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
