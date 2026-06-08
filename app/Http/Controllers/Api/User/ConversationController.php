<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Events\ChatTyping;
use App\Http\Controllers\Controller;
use App\Http\Resources\Chat\ChatConversationResource;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\ConversationStatus;
use App\Models\ConversationType;
use App\Models\Message;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ConversationController extends Controller
{
    /**
     * Get all conversations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $validatedData = $request->validate([
            'type' => ['nullable', 'string', 'in:direct,support'],
            'participant_type' => ['nullable', 'string', 'in:vendor,admin,consumer'],
            'status' => ['nullable', 'string', 'in:open,closed'],
            'q' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Conversation::query()
            ->whereHas('participants', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->with(['participants.user.vendorProfile'])
            ->with(['messages' => function (Relation $query): void {
                $query->latest()->limit(1);
            }])
            ->withCount([
                'messages as unread_messages_count' => function (Builder $query) use ($user): void {
                    $query->where('sender_id', '!=', $user->id)
                        ->where('is_read', false);
                },
            ])
            ->latest('updated_at')
            ->latest('id');

        if (($validatedData['type'] ?? null) !== null) {
            $query->where(
                'conversation_type_id',
                $this->conversationTypeId($validatedData['type'])
            );
        }

        if (($validatedData['status'] ?? null) !== null) {
            $query->where(
                'conversation_status_id',
                $this->conversationStatusId($validatedData['status'])
            );
        }

        if (($validatedData['participant_type'] ?? null) !== null) {
            $participantTypeId = $this->userTypeId($validatedData['participant_type']);

            $query->whereHas(
                'participants.user',
                function (Builder $query) use ($user, $participantTypeId): void {
                    $query->where('users.id', '!=', $user->id)
                        ->where('users.user_type_id', $participantTypeId);
                }
            );
        }

        if (($validatedData['q'] ?? null) !== null) {
            $search = trim($validatedData['q']);

            if ($search !== '') {
                $query->whereHas(
                    'participants.user',
                    function (Builder $query) use ($user, $search): void {
                        $query->where('users.id', '!=', $user->id)
                            ->where(function (Builder $query) use ($search): void {
                                $query->where('users.first_name', 'like', '%'.$search.'%')
                                    ->orWhere('users.last_name', 'like', '%'.$search.'%')
                                    ->orWhere('users.email', 'like', '%'.$search.'%')
                                    ->orWhereHas(
                                        'vendorProfile',
                                        function (Builder $query) use ($search): void {
                                            $query->where('business_name', 'like', '%'.$search.'%');
                                        }
                                    );
                            });
                    }
                );
            }
        }

        $conversations = $query->simplePaginate($request->integer('per_page', 15));

        return static::successResponse(
            ChatConversationResource::collection($conversations),
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
            $conversation = Conversation::query()
                ->where('conversation_type_id', ConversationType::SUPPORT_ID)
                ->where('conversation_status_id', ConversationStatus::OPEN_ID)
                ->whereHas(
                    'participants',
                    function (Builder $query) use ($user): void {
                        $query->where('user_id', $user->id);
                    }
                )
                ->first();

            if (! $conversation) {
                $conversation = Conversation::create([
                    'conversation_type_id' => ConversationType::SUPPORT_ID,
                    'conversation_status_id' => ConversationStatus::OPEN_ID,
                ]);

                $participants = [[
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]];

                $admins = User::where('user_type_id', UserType::ADMIN_ID)
                    ->where('user_status_id', UserStatus::ACTIVE_ID)
                    ->get();

                foreach ($admins as $admin) {
                    $participants[] = [
                        'conversation_id' => $conversation->id,
                        'user_id' => $admin->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
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
            $conversation = Conversation::where('conversation_type_id', ConversationType::DIRECT_ID)
                ->whereHas(
                    'participants',
                    function (Builder $query) use ($user): void {
                        $query->where('user_id', $user->id);
                    }
                )
                ->whereHas(
                    'participants',
                    function (Builder $query) use ($targetUserId): void {
                        $query->where('user_id', $targetUserId);
                    }
                )
                ->first();

            if (! $conversation) {
                $conversation = Conversation::create([
                    'conversation_type_id' => ConversationType::DIRECT_ID,
                    'conversation_status_id' => ConversationStatus::OPEN_ID,
                ]);

                ConversationParticipant::insert([[
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                    [
                        'conversation_id' => $conversation->id,
                        'user_id' => $targetUserId,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]]);
            }
        }

        $conversation->load([
            'participants.user.vendorProfile',
            'messages' => function (Relation $query): void {
                $query->latest()->limit(1);
            },
        ])->loadCount([
            'messages as unread_messages_count' => function (Builder $query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                    ->where('is_read', false);
            },
        ]);

        return static::successResponse(
            new ChatConversationResource($conversation),
            __('api.chat.conversation_retrieved')
        );
    }

    /**
     * Get a specific conversation.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $conversation = Conversation::query()
            ->with(['participants.user.vendorProfile'])
            ->with(['messages' => function (Relation $query): void {
                $query->latest()->limit(1);
            }])
            ->withCount([
                'messages as unread_messages_count' => function (Builder $query) use ($user) {
                    $query->where('sender_id', '!=', $user->id)
                        ->where('is_read', false);
                },
            ])
            ->where('id', $id)
            ->whereHas('participants', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        if (! $conversation) {
            abort(404, __('api.general.not_found'));
        }

        return static::successResponse(
            new ChatConversationResource($conversation),
            __('api.chat.conversation_retrieved')
        );
    }

    /**
     * Get total unread message count for the user.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $count = Message::query()
            ->whereHas(
                'conversation.participants',
                function (Builder $query) use ($user): void {
                    $query->where('user_id', $user->id);
                }
            )
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

        $conversation = Conversation::query()
            ->where('id', $validatedData['conversation_id'])
            ->whereHas(
                'participants',
                function (Builder $query) use ($user): void {
                    $query->where('user_id', $user->id);
                }
            )
            ->first();

        if (! $conversation) {
            abort(404, __('api.general.not_found'));
        }

        if (
            (int) $conversation->conversation_type_id === ConversationType::SUPPORT_ID &&
            (int) $conversation->conversation_status_id === ConversationStatus::CLOSED_ID
        ) {
            abort(422, __('api.chat.conversation_resolved'));
        }

        broadcast(new ChatTyping($conversation->id, $user->id))->toOthers();

        return static::successResponse(message: __('api.chat.typing'));
    }

    /**
     * Conversation Type
     */
    private function conversationTypeId(string $type): int
    {
        return $type === 'support'
            ? ConversationType::SUPPORT_ID
            : ConversationType::DIRECT_ID;
    }

    /**
     * Conversation Status
     */
    private function conversationStatusId(string $status): int
    {
        return $status === 'closed'
            ? ConversationStatus::CLOSED_ID
            : ConversationStatus::OPEN_ID;
    }

    /**
     * User Type
     */
    private function userTypeId(string $type): int
    {
        return match ($type) {
            'admin' => UserType::ADMIN_ID,
            'vendor' => UserType::VENDOR_ID,
            'consumer' => UserType::CONSUMER_ID,
            default => UserType::CONSUMER_ID,
        };
    }
}
