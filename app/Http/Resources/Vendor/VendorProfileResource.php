<?php

declare(strict_types=1);

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->vendorProfile;

        return [
            'id' => $this->id,
            'name' => trim($this->first_name.' '.$this->last_name),
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'business_name' => $profile?->business_name,
            'contact_phone' => $profile?->contact_phone,
            'city' => $profile?->city,
            'province' => $profile?->province,
            'address' => $profile?->address,
            'is_verified' => (bool) $profile?->is_verified,
            'meta' => $profile?->meta,
            'status_id' => $this->user_status_id,
            'type_id' => $this->user_type_id,
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
