<?php

declare(strict_types=1);

namespace App\Http\Resources\Chat;

use App\Http\Resources\User\UserResource;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Override;

/**
 * @mixin Message
 */
class MessageResource extends JsonResource
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
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'content' => $this->content,
            'file_path' => $this->file_path,
            'file_url' => $this->file_path && Storage::disk('public')->exists($this->file_path)
                ? Storage::disk('public')->url($this->file_path)
                : null,
            'is_read' => $this->is_read,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
