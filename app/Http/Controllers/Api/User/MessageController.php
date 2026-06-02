<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Constants\StorageDirectory;
use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Chat\MessageResource;
use App\Models\Conversation;
use App\Models\ConversationStatus;
use App\Models\ConversationType;
use App\Models\Message;
use App\Notifications\NewChatMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class MessageController extends Controller
{
    /**
     * Get message history for a conversation.
     */
    public function index(Request $request, int $conversationId): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('participants', static fn (Builder $query) => $query->where('user_id', $user->id))
            ->first();

        if (! $conversation) {
            abort(404, __('api.chat.conversation_not_found'));
        }

        // Mark unread messages from others as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->get();

        return static::successResponse(
            MessageResource::collection($messages),
            __('api.chat.messages_retrieved')
        );
    }

    /**
     * Send a message.
     */
    public function store(Request $request, int $conversationId): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validate([
            'message' => ['nullable', 'string', 'max:1200'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:png,jpg,jpeg,pdf'],
        ]);

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas(
                'participants',
                static function (Builder $query) use ($user): void {
                    $query->where('user_id', $user->id);
                }
            )
            ->first();

        if (! $conversation) {
            abort(404, __('api.chat.conversation_not_found'));
        }

        if (
            (int) $conversation->conversation_type_id === ConversationType::SUPPORT_ID &&
            (int) $conversation->conversation_status_id === ConversationStatus::CLOSED_ID
        ) {
            abort(422, __('api.chat.conversation_resolved'));
        }

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store(StorageDirectory::CHAT_ATTACHMENTS, 'public');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $validatedData['message'] ?? '',
            'file_path' => $filePath,
        ]);

        $conversation->touch();

        broadcast(new ChatMessageSent($message))->toOthers();

        $recipients = $conversation->participants()
            ->where('user_id', '!=', $user->id)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        Notification::sendNow($recipients, new NewChatMessage($message));

        $message->load('sender');

        return static::successResponse(
            new MessageResource($message),
            __('api.chat.message_sent')
        );
    }
}
