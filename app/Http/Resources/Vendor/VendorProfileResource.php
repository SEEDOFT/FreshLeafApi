<?php

declare(strict_types=1);

namespace App\Http\Resources\Vendor;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
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
            'village' => $profile->village,
            'commune' => $profile->commune,
            'district' => $profile->district,
            'province' => $profile->province,
            'address' => $profile->address,
            'is_verified' => (bool) $profile->is_verified,
            'shop_description' => $profile->shop_description,
            'store_front_image' => $profile->store_front_image
                ? Storage::disk('public')->url($profile->store_front_image)
                : null,
            'opening_time' => $profile->opening_time,
            'closing_time' => $profile->closing_time,
            'is_open' => (bool) $profile->is_open,
            'product_count' => $this->active_inventories_count ??
                $this->vendorInventories()->active()->count(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
