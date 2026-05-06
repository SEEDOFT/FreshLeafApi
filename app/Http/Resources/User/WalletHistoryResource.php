<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WalletHistory
 */
class WalletHistoryResource extends JsonResource
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
            'wallet_id' => $this->wallet_id,
            'user_id' => $this->user_id,
            'currency_id' => $this->currency_id,
            'balance' => $this->balance,
            'currency' => $this->relationLoaded('currency')
                ? new CurrencyResource($this->currency)
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
