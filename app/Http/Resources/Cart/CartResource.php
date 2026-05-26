<?php

declare(strict_types=1);

namespace App\Http\Resources\Cart;

use App\Http\Resources\Product\VendorInventoryResource;
use App\Models\Cart;
use App\Models\Currency;
use App\Models\VendorInventory;
use App\Services\MoneyService;
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
