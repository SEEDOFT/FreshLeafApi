<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Http\Resources\Shared\StatusResource;
use App\Http\Resources\Shared\TypeResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin PaymentMethod
 */
class PaymentMethodResource extends JsonResource
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
            'label' => $this->label,
            'card_holder_name' => $this->card_holder_name,
            'card_number' => '**** **** **** '.substr((string) $this->card_number, -4),
            'expiry_month' => $this->expiry_month,
            'expiry_year' => $this->expiry_year,
            'is_default' => $this->is_default,
            'billing_address' => $this->billing_address,
            'billing_city' => $this->billing_city,
            'billing_state' => $this->billing_state,
            'billing_zip_code' => $this->billing_zip_code,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'type' => new TypeResource($this->whenLoaded('type')),
            'status' => new StatusResource($this->whenLoaded('status')),
        ];
    }
}
