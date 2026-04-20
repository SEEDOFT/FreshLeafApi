<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->adminProfile;

        return [
            'id' => $this->id,
            'name' => \trim($this->first_name.' '.$this->last_name),
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'department' => $profile?->department,
            'job_title' => $profile?->job_title,
            'office_phone' => $profile?->office_phone,
            'super_admin' => (bool) $profile?->super_admin,
            'permissions' => $profile?->permissions,
            'status_id' => $this->user_status_id,
            'type_id' => $this->user_type_id,
            'updated_at' => \optional($this->updated_at)->toIso8601String(),
        ];
    }
}
