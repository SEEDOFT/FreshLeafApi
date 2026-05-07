<?php

declare(strict_types=1);

namespace App\Http\Resources\Cart;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items');
        $total = 0;
        if ($items && is_iterable($items)) {
            foreach ($items as $item) {
                $total += (float) $item->subtotal;
            }
        }

        return [
            'id' => $this->id,
            'status' => $this->whenLoaded('status', fn () => [
                'id' => $this->user_cart_status_id,
                'name' => $this->status->name,
            ]),
            'type' => $this->whenLoaded('type', fn () => [
                'id' => $this->user_cart_type_id,
                'name' => $this->type->name,
            ]),
            'items' => CartItemResource::collection($items),
            'total' => round($total, 2),
            'created_at' => \optional($this->created_at)->toIso8601String(),
            'updated_at' => \optional($this->updated_at)->toIso8601String(),
        ];
    }
}
