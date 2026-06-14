<?php

declare(strict_types=1);

namespace App\Http\Resources\Rating;

use App\Models\VendorInventoryRating;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property VendorInventoryRating $resource
 */
class VendorInventoryRatingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'user_name' => $this->resource->user->fullName,
            'vendor_inventory_id' => $this->resource->vendor_inventory_id,
            'rating' => $this->resource->rating,
            'review' => $this->resource->review,
            'created_at' => $this->resource->created_at?->toISOString(),
        ];
    }
}
