<?php

namespace App\Http\Resources\Admin;

use App\Models\VendorStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingVendorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'business_name' => $this->business_name,
            'contact_phone' => $this->contact_phone,
            'city' => $this->city,
            'province' => $this->province,
            'address' => $this->address,
            'is_verified' => (bool) $this->is_verified,
            'status_id' => $this->status_id,
            'status' => match ((int) $this->status_id) {
                VendorStatus::ACTIVE => 'active',
                VendorStatus::INACTIVE => 'inactive',
                VendorStatus::PENDING => 'pending',
                VendorStatus::SUSPENDED => 'suspended',
                VendorStatus::REJECTED => 'rejected',
                default => 'unknown',
            },
            'submitted_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
