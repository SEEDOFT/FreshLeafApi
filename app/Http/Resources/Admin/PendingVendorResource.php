<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Http\Resources\User\UserStatusResource;
use App\Http\Resources\User\UserTypeResource;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 * @mixin VendorProfile
 */
class PendingVendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
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
            'approved_at' => $profile?->approved_at,
            'approved_by_admin_id' => $profile?->approved_by_admin_id,
            'approve_reason' => $profile?->approve_reason,
            'rejected_at' => $profile?->rejected_at,
            'rejected_by_admin_id' => $profile?->rejected_by_admin_id,
            'reject_reason' => $profile?->reject_reason,
            'status' => $this->whenLoaded('status')
                ? UserStatusResource::make($this->status) : null,
            'type' => $this->whenLoaded('type')
                ? UserTypeResource::make($this->type) : null,
            'submitted_at' => $this->created_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
