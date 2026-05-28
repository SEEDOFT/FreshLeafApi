<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Override;

/**
 * @mixin SupportMessage
 */
class SupportMessageResource extends JsonResource
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'support_ticket_id' => $this->support_ticket_id,
            'sender_type' => $this->sender_type,
            'sender_id' => $this->sender_id,
            'message' => $this->message,
            'file_path' => $this->file_path ? Storage::url($this->file_path) : null,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
