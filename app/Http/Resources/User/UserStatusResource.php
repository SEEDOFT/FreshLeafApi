<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserStatus
 */
class UserStatusResource extends JsonResource
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
            'name' => translate($this->name_en, $this->name_km),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
