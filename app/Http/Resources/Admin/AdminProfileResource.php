<?php

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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'job_title' => $this->job_title,
            'office_phone' => $this->office_phone,
            'super_admin' => (bool) $this->super_admin,
            'permissions' => $this->permissions,
            'status_id' => $this->status_id,
            'type_id' => $this->type_id,
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
