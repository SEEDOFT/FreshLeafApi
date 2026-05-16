<?php

declare(strict_types=1);

namespace App\Http\Resources\Wishlist;

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
            'status' => $this->whenLoaded(
                'status',
                fn () => [
                    'id' => $this->status->id,
                    'name' => $this->status->name_en,
                ]
            ),
            'product' => $this->whenLoaded(
                'vendorInventory',
                fn () => $this->vendorInventory?->product
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
