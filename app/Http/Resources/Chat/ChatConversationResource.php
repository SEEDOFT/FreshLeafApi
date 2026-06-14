<?php

declare(strict_types=1);

namespace App\Http\Resources\Chat;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\ConversationStatus;
use App\Models\ConversationType;
use App\Models\Message;
use App\Models\User;
use App\Models\UserType;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Override;

/**
 * @mixin Conversation
 */
class ChatConversationResource extends JsonResource
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $authUserId = (int) $request->user()?->id;
        $otherParticipant = $this->participants
            ->first(
                static fn (ConversationParticipant $participant): bool => (int) $participant->user_id !== $authUserId
            )
            ?->user;
        $latestMessage = $this->messages->first();

        return [
            'id' => $this->id,
            'type' => $this->conversationTypeName(),
            'status' => $this->conversationStatusName(),
            'other_participant' => $otherParticipant instanceof User
                ? $this->participantData($otherParticipant)
                : null,
            'latest_message' => $latestMessage instanceof Message
                ? $this->messageData($latestMessage)
                : null,
            'unread_count' => (int) ($this->unread_messages_count ?? 0),
            'deep_link' => 'freshleaf://support-chat?conversation_id='.$this->id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function participantData(User $user): array
    {
        /** @var VendorProfile|null $vendorProfile */
        $vendorProfile = $user->vendorProfile;

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->fullName,
            'type' => $this->userTypeName((int) $user->user_type_id),
            'image' => $user->image ? Storage::url($user->image) : null,
            'business_name' => $vendorProfile?->business_name,
            'store_front_image' => $vendorProfile?->store_front_image
                && Storage::disk('public')->exists($vendorProfile->store_front_image)
                ? Storage::disk('public')->url($vendorProfile->store_front_image)
                : null,
            'is_verified' => (bool) ($vendorProfile?->is_verified),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function messageData(Message $message): array
    {
        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'content' => $message->content,
            'has_attachment' => (bool) $message->file_path,
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }

    private function conversationTypeName(): string
    {
        return match ((int) $this->conversation_type_id) {
            ConversationType::DIRECT_ID => 'direct',
            ConversationType::SUPPORT_ID => 'support',
            default => (string) $this->conversation_type_id,
        };
    }

    private function conversationStatusName(): string
    {
        return match ((int) $this->conversation_status_id) {
            ConversationStatus::OPEN_ID => 'open',
            ConversationStatus::CLOSED_ID => 'closed',
            default => (string) $this->conversation_status_id,
        };
    }

    private function userTypeName(int $userTypeId): string
    {
        return match ($userTypeId) {
            UserType::ADMIN_ID => 'admin',
            UserType::VENDOR_ID => 'vendor',
            UserType::CONSUMER_ID => 'consumer',
            default => (string) $userTypeId,
        };
    }
}
