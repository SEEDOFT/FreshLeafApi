<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Events\ChatTyping;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ConversationController extends Controller
{
    /**
     * Get all conversations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $conversations = Conversation::query()
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['participants.user', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest('updated_at')
            ->simplePaginate($request->integer('per_page', 15));

        return static::successResponse(
            $conversations,
            __('api.chat.conversations_retrieved')
        );
    }

    /**
     * Start a new conversation or get existing one.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validate([
            'type' => ['required', 'string', 'in:direct,support'],
            'user_id' => ['required_if:type,direct', 'nullable', 'integer', 'exists:users,id'],
        ]);

        $type = $validatedData['type'];

        if ($type === 'support') {
            // Check for existing open support conversation
            $conversation = Conversation::where('conversation_type_id', \App\Models\ConversationType::SUPPORT_ID)
                ->where('conversation_status_id', \App\Models\ConversationStatus::OPEN_ID)
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->first();

            if (! $conversation) {
                $conversation = Conversation::create([
                    'conversation_type_id' => \App\Models\ConversationType::SUPPORT_ID,
                    'conversation_status_id' => \App\Models\ConversationStatus::OPEN_ID,
                ]);

                $participants = [
                    [
                        'conversation_id' => $conversation->id,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ];

                $admins = User::where('user_type_id', UserType::ADMIN_ID)
                    ->where('user_status_id', UserStatus::ACTIVE_ID)
                    ->get();
                
                foreach ($admins as $admin) {
                    $participants[] = [
                        'conversation_id' => $conversation->id,
                        'user_id' => $admin->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                ConversationParticipant::insert($participants);
            }
        } else {
            // Direct chat (Consumer to Vendor, etc.)
            $targetUserId = (int) $validatedData['user_id'];

            if ($targetUserId === $user->id) {
                abort(400, __('api.chat.cannot_chat_with_self'));
            }

            // Find conversation where exactly these two users are participants
            // For simplicity, we just look for a direct conversation that has both.
            $conversation = Conversation::where('conversation_type_id', \App\Models\ConversationType::DIRECT_ID)
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereHas('participants', function ($query) use ($targetUserId) {
                    $query->where('user_id', $targetUserId);
                })
                ->first();

            if (! $conversation) {
                $conversation = Conversation::create([
                    'conversation_type_id' => \App\Models\ConversationType::DIRECT_ID,
                    'conversation_status_id' => \App\Models\ConversationStatus::OPEN_ID,
                ]);

                ConversationParticipant::insert([
                    ['conversation_id' => $conversation->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
                    ['conversation_id' => $conversation->id, 'user_id' => $targetUserId, 'created_at' => now(), 'updated_at' => now()],
                ]);
            }
        }

        $conversation->load('participants.user');

        return static::successResponse(
            $conversation,
            __('api.chat.conversation_retrieved')
        );
    }

    /**
     * Get a specific conversation.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $conversation = Conversation::with(['participants.user'])
            ->where('id', $id)
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->firstOrFail();

        return static::successResponse(
            $conversation,
            __('api.chat.conversation_retrieved')
        );
    }

    /**
     * Get total unread message count for the user.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $count = Message::whereHas('conversation.participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

        return static::successResponse([
            'count' => $count,
        ], __('api.chat.unread_retrieved'));
    }

    /**
     * Broadcast typing event.
     */
    public function sendTyping(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
        ]);

        $conversation = Conversation::where('id', $validatedData['conversation_id'])
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->firstOrFail();

        broadcast(new ChatTyping($conversation->id, $user->id))->toOthers();

        return static::successResponse([], __('api.chat.typing'));
    }
}
