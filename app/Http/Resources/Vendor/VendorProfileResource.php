<?php

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
            'meta' => $this->meta,
            'status_id' => $this->status_id,
            'type_id' => $this->type_id,
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
