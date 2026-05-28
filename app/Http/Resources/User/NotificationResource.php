<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Http\Resources\Shared\StatusResource;
use App\Http\Resources\Shared\TypeResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
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
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'read_at' => $this->read_at?->toIso8601String(),
            'is_read' => $this->read_at !== null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            'type' => new TypeResource($this->whenLoaded('type')),
            'status' => new StatusResource($this->whenLoaded('status')),
        ];
    }
}
