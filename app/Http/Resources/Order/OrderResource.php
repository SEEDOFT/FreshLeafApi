<?php

declare(strict_types=1);

namespace App\Http\Resources\Order;

use App\Http\Resources\Shared\StatusResource;
use App\Http\Resources\Shared\TypeResource;
use App\Models\Order;
use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'address_id' => $this->address_id,
            'order_type_id' => $this->order_type_id,
            'order_status_id' => $this->order_status_id,
            'payment_status_id' => $this->payment_status_id,
            'delivery_date' => $this->delivery_date->format('Y-m-d'),
            'delivery_slot' => $this->delivery_slot,
            'subtotal' => MoneyService::money($this->subtotal),
            'subtotal_display' => MoneyService::displayTotalsFromUsd($this->subtotal),
            'discount_amount' => MoneyService::money($this->discount_amount),
            'discount_amount_display' => MoneyService::displayTotalsFromUsd($this->discount_amount),
            'delivery_fee' => MoneyService::money($this->delivery_fee),
            'delivery_fee_display' => MoneyService::displayTotalsFromUsd($this->delivery_fee),
            'tax_amount' => MoneyService::money($this->tax_amount),
            'tax_amount_display' => MoneyService::displayTotalsFromUsd($this->tax_amount),
            'total_amount' => MoneyService::money($this->total_amount),
            'total_amount_display' => MoneyService::displayTotalsFromUsd($this->total_amount),
            'notes' => $this->notes,
            'place_order_date' => $this->place_order_date?->toIso8601String(),
            'order_pending_date' => $this->order_pending_date?->toIso8601String(),
            'order_confirmed_date' => $this->order_confirmed_date?->toIso8601String(),
            'order_preparing_date' => $this->order_preparing_date?->toIso8601String(),
            'order_delivered_date' => $this->order_delivered_date?->toIso8601String(),
            'order_cancelled_date' => $this->order_cancelled_date?->toIso8601String(),
            'order_awaiting_payment_date' => $this->order_awaiting_payment_date?->toIso8601String(),
            'currency_id' => $this->currency_id,
            'payment_id' => $this->payment_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'preparation_proof_photo' => $this->preparation_proof_photo ? asset('storage/'.$this->preparation_proof_photo) : null,
            'delivery_company_name' => $this->delivery_company_name,
            'delivery_tracking_info' => $this->delivery_tracking_info,

            'status' => new StatusResource($this->whenLoaded('status')),
            'payment_status' => new StatusResource($this->whenLoaded('paymentStatus')),
            'type' => new TypeResource($this->whenLoaded('type')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
