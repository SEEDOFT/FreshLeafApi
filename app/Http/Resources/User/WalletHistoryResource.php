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
            'action' => $this->action,
            'amount_before' => $this->amount_before,
            'amount_change' => $this->amount_change,
            'amount_after' => $this->amount_after,
            'performed_by_user_id' => $this->performed_by_user_id,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'description' => $this->description,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
