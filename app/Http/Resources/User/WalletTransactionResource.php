<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Http\Resources\Shared\StatusResource;
use App\Http\Resources\Shared\TypeResource;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin WalletTransaction
 */
class WalletTransactionResource extends JsonResource
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
            'wallet_id' => $this->wallet_id,
            'amount' => (float) $this->amount,
            'payment_method_id' => $this->payment_method_id,
            'reference_id' => $this->reference_id,
            'reference_type' => $this->reference_type,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'type' => new TypeResource($this->whenLoaded('type')),
            'status' => new StatusResource($this->whenLoaded('status')),
        ];
    }
}
