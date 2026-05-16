<?php

declare(strict_types=1);

namespace App\Http\Resources\Vendor;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin User
 * @mixin VendorProfile
 */
class VendorProfileResource extends JsonResource
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $profile = $this->vendorProfile;

        return [
            'id' => $this->id,
            'name' => $this->fullName,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'business_name' => $profile->business_name,
            'contact_phone' => $profile->contact_phone,
            'city' => $profile->city,
            'province' => $profile->province,
            'address' => $profile->address,
            'is_verified' => (bool) $profile->is_verified,
            'meta' => $profile->meta,
            'locale' => $profile->locale,
            'theme' => $profile->theme,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
