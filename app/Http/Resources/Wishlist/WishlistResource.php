<?php

declare(strict_types=1);

namespace App\Http\Resources\Wishlist;

use App\Http\Resources\Product\VendorInventoryResource;
use App\Http\Resources\Shared\StatusResource;
use App\Models\Wishlist;
use App\Models\WishlistStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Wishlist
 * @mixin WishlistStatus
 */
class WishlistResource extends JsonResource
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
            'status' => new StatusResource($this->whenLoaded('status')),
            'vendor_inventory' => $this->whenLoaded(
                'vendorInventory',
                fn () => new VendorInventoryResource($this->vendorInventory),
                null
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
