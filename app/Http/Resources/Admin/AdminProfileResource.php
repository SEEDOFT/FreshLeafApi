<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 * @mixin AdminProfile
 */
class AdminProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
