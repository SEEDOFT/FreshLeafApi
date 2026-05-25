<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Override;

/**
 * @mixin User
 * @mixin UserProfile
 */
class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'image' => $this->when(
                $this->image !== null,
                fn () => Storage::disk('public')->url($this->image),
                null
            ),
            'set_pin' => (bool) ($this->userProfile->pin ?? false),
            'locale' => $this->userProfile->locale,
            'theme' => $this->userProfile->theme,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
