<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\PaymentMethodType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaymentMethodType
 */
class PaymentMethodTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $code = match ($this->id) {
            PaymentMethodType::WALLET_ID => 'wallet',
            PaymentMethodType::CREDIT_DEBIT_ID => 'credit_debit',
            PaymentMethodType::ABA_ID => 'aba',
            PaymentMethodType::ACLEDA_ID => 'acleda',
            PaymentMethodType::COD_ID => 'cod',
            default => strtolower(str_replace(' ', '_', $this->name_en)),
        };

        return [
            'id' => $this->id,
            'code' => $code,
            'name' => translate($this->name_en, $this->name_km),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
