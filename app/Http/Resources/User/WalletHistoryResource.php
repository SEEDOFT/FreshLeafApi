<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Http\Resources\Shared\CurrencyResource;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin WalletHistory
 */
class WalletHistoryResource extends JsonResource
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
            'user_id' => $this->user_id,
            'balance' => (float) $this->balance,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
        ];
    }
}
