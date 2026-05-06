<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin SupportMessage
 */
class SupportMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'support_ticket_id' => $this->support_ticket_id,
            'sender_type' => $this->sender_type,
            'sender_id' => $this->sender_id,
            'message' => $this->message,
            'is_read' => (bool) $this->is_read,
            'file_path' => $this->file_path ? Storage::url($this->file_path) : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
