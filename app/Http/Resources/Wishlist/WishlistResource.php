<?php

declare(strict_types=1);

namespace App\Http\Resources\Wishlist;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Wishlist
 */
class WishlistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->whenLoaded('status', fn () => [
                'id' => $this->user_wishlist_status_id,
                'name' => $this->status->name,
            ]),
            'type' => $this->whenLoaded('type', fn () => [
                'id' => $this->user_wishlist_type_id,
                'name' => $this->type->name,
            ]),
            'items' => WishlistItemResource::collection($this->whenLoaded('items')),
            'created_at' => \optional($this->created_at)->toIso8601String(),
            'updated_at' => \optional($this->updated_at)->toIso8601String(),
        ];
    }
}
