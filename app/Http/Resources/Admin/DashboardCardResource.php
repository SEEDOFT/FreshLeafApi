<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => (string) ($this['label'] ?? ''),
            'value' => (int) ($this['value'] ?? 0),
            'tone' => (string) ($this['tone'] ?? 'neutral'),
        ];
    }
}
