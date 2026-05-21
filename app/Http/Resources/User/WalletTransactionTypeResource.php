<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\WalletTransactionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin WalletTransactionType
 */
class WalletTransactionTypeResource extends JsonResource
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
            'name' => translate($this->name_en, $this->name_km),
        ];
    }
}
