<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 * @mixin UserProfile
 */
class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'image' => $this->image,
            'set_pin' => (bool) $this->userProfile->pin,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
