<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Get message history for a conversation.
     */
    public function index(Request $request, int $conversationId): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->firstOrFail();

        // Mark unread messages from others as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->get();

        return static::successResponse(
            $messages,
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
            'message' => ['required', 'string', 'max:1200'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:png,jpg,jpeg,pdf'],
        ]);

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->firstOrFail();

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('chat/files', 'public');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $validatedData['message'],
            'file_path' => $filePath,
        ]);

        $conversation->touch();

        broadcast(new ChatMessageSent($message))->toOthers();

        $otherParticipants = $conversation->participants()->where('user_id', '!=', $user->id)->with('user')->get();
        foreach ($otherParticipants as $participant) {
            if ($participant->user) {
                $participant->user->notify(new NewChatMessage($message));
            }
        }

        $message->load('sender');

        return static::successResponse(
            $message,
            __('api.chat.message_sent')
        );
    }
}
