<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\User;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
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
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'read_at' => $this->read_at,
            'is_read' => $this->read_at !== null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'type' => $this->whenLoaded('type', fn () => [
                'id' => $this->type->id,
                'code' => $this->type->code,
                'name_en' => $this->type->name_en,
                'name_km' => $this->type->name_km,
            ]),
            'status' => $this->whenLoaded('status', fn () => [
                'id' => $this->status->id,
                'code' => $this->status->code,
                'name_en' => $this->status->name_en,
                'name_km' => $this->status->name_km,
            ]),
        ];
    }
}
