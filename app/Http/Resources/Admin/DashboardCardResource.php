<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
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
