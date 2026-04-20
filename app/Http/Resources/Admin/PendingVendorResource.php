<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingVendorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->vendorProfile;

        return [
            'id' => $this->id,
            'name' => \trim($this->first_name.' '.$this->last_name),
            'email' => $this->email,
            'business_name' => $profile?->business_name,
            'contact_phone' => $profile?->contact_phone,
            'city' => $profile?->city,
            'province' => $profile?->province,
            'address' => $profile?->address,
            'is_verified' => (bool) $profile?->is_verified,
            'status_id' => $this->user_status_id,
            'status' => match ((int) $this->user_status_id) {
                UserStatus::ACTIVE => 'active',
                UserStatus::INACTIVE => 'inactive',
                UserStatus::PENDING => 'pending',
                default => 'unknown',
            },
            'submitted_at' => \optional($this->created_at)->toIso8601String(),
            'updated_at' => \optional($this->updated_at)->toIso8601String(),
        ];
    }
}
