<?php

declare(strict_types=1);

namespace App\Http\Resources\Order;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'address_id' => $this->address_id,
            'order_type_id' => $this->order_type_id,
            'order_status_id' => $this->order_status_id,
            'payment_status_id' => $this->payment_status_id,
            'delivery_date' => $this->delivery_date?->format('Y-m-d'),
            'delivery_slot' => $this->delivery_slot,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'delivery_fee' => $this->delivery_fee,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'status' => $this->whenLoaded('status', fn() => [
                'id' => $this->status->id,
                'name' => $this->status->name,
                'translated_name' => $this->status->translated_name,
            ]),
            'payment_status' => $this->whenLoaded('paymentStatus', fn() => [
                'id' => $this->paymentStatus->id,
                'name' => $this->paymentStatus->name,
                'translated_name' => $this->paymentStatus->translated_name,
            ]),
            'type' => $this->whenLoaded('type', fn() => [
                'id' => $this->type->id,
                'name' => $this->type->name,
                'translated_name' => $this->type->translated_name,
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
