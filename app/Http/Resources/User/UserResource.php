<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Http\Resources\Shared\StatusResource;
use App\Http\Resources\Shared\TypeResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Override;

/**
 * @mixin User
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
            'full_name' => $this->fullName,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'image' => $this->image ? Storage::url($this->image) : null,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'locale' => $this->currentLocale,
            'theme' => $this->currentTheme,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'type' => new TypeResource($this->whenLoaded('type')),
            'status' => new StatusResource($this->whenLoaded('status')),
            'profile' => $this->whenLoaded('userProfile', fn () => [
                'id' => $this->userProfile->id,
                'gender' => $this->userProfile->gender,
                'date_of_birth' => $this->userProfile->date_of_birth?->toIso8601String(),
                'locale' => $this->userProfile->locale,
                'theme' => $this->userProfile->theme,
                'has_pin' => $this->userProfile->hasPin(),
            ]),
        ];
    }
}
