<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Events\ChatMessageSent;
use App\Events\ChatTyping;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\ConversationStatus;
use App\Models\ConversationType;
use App\Models\Message;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Notifications\NewChatMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatInbox extends Component
{
    use WithFileUploads;

    #[Url]
    public ?int $activeConversationId = null;

    public string $message = '';

    /** @var mixed */
    public $file;

    public string $activeTab = 'all';

    public string $conversationFilter = 'all';

    public bool $showHistory = true;

    protected const string FUNC_HANDLE_INCOMING_MESSAGE = 'handleIncomingMessage';

    protected const string FUNC_HANDLE_TYPING_EVENT = 'handleTypingEvent';

    public function mount(): void
    {
        $this->showHistory = (bool) session('chat_show_history', true);

        $user = Auth::user();
        if ($user && $user->user_type_id === UserType::VENDOR_ID) {
            $conversation = Conversation::where('conversation_type_id', ConversationType::SUPPORT_ID)
                ->where('conversation_status_id', ConversationStatus::OPEN_ID)
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->first();

            if ($conversation instanceof Conversation && ! $this->activeConversationId) {
                $this->activeConversationId = $conversation->id;
            }
        }
    }

    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
        session(['chat_show_history' => $this->showHistory]);
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        $userId = Auth::id();

        $query = Conversation::query()
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['participants.user.type', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($userId) {
                    $query->where('sender_id', '!=', $userId)
                        ->where('is_read', false);
                },
            ])
            ->latest('updated_at');

        if ($this->conversationFilter === 'support_open') {
            $query->where('conversation_type_id', ConversationType::SUPPORT_ID)
                ->where('conversation_status_id', ConversationStatus::OPEN_ID);
        } elseif ($this->conversationFilter === 'support_resolved') {
            $query->where('conversation_type_id', ConversationType::SUPPORT_ID)
                ->where('conversation_status_id', ConversationStatus::CLOSED_ID);
        } elseif ($this->conversationFilter === 'direct') {
            $query->where('conversation_type_id', ConversationType::DIRECT_ID);
        }

        if ($this->activeTab !== 'all') {
            $typeId = match ($this->activeTab) {
                'admins' => UserType::ADMIN_ID,
                'vendors' => UserType::VENDOR_ID,
                'consumers' => UserType::CONSUMER_ID,
                default => null,
            };

            if ($typeId === null) {
                return collect();
            }

            $query->whereHas('participants', function ($q) use ($typeId) {
                $q->where('user_id', '!=', Auth::id())
                    ->whereHas('user', function ($u) use ($typeId) {
                        $u->where('user_type_id', $typeId);
                    });
            });
        }

        return $query->get();
    }

    public function selectConversation(int $id): void
    {
        $conversation = $this->participantConversationQuery()
            ->where('id', $id)
            ->firstOrFail();

        $this->activeConversationId = $conversation->id;

        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->dispatch('conversation-selected');
    }

    public function sendMessage(): void
    {
        if (trim($this->message) === '' && ! $this->file) {
            return;
        }

        if (! $this->activeConversationId) {
            return;
        }

        $conversation = $this->participantConversationQuery()
            ->where('id', $this->activeConversationId)
            ->firstOrFail();

        if ($this->isResolvedSupportConversation($conversation)) {
            $this->addError('message', __('api.chat.conversation_resolved'));

            return;
        }

        $filePath = null;
        if ($this->file) {
            $filePath = $this->file->store('chat/files', 'public');
        }

        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'content' => trim($this->message),
            'file_path' => $filePath,
        ]);

        $conversation->touch();

        if (request()->header('X-Socket-ID') === 'undefined') {
            request()->headers->remove('X-Socket-ID');
        }

        broadcast(new ChatMessageSent($msg))->toOthers();

        $recipients = $conversation->participants()
            ->where('user_id', '!=', Auth::id())
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        Notification::sendNow($recipients, new NewChatMessage($msg));

        $this->message = '';
        $this->file = null;
        $this->dispatch('message-sent');
    }

    public function sendTyping(): void
    {
        if ($this->activeConversationId) {
            $conversation = $this->participantConversationQuery()
                ->where('id', $this->activeConversationId)
                ->first();

            if (! $conversation || $this->isResolvedSupportConversation($conversation)) {
                return;
            }

            if (request()->header('X-Socket-ID') === 'undefined') {
                request()->headers->remove('X-Socket-ID');
            }

            broadcast(
                new ChatTyping(
                    $this->activeConversationId,
                    (int) Auth::id()
                )
            )->toOthers();
        }
    }

    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        if (! $this->activeConversationId) {
            return [];
        }

        return [
            "echo-private:chat.conversation.{$this->activeConversationId},ChatMessageSent" => self::FUNC_HANDLE_INCOMING_MESSAGE,
            "echo-private:chat.conversation.{$this->activeConversationId},ChatTyping" => self::FUNC_HANDLE_TYPING_EVENT,
        ];
    }

    public function handleIncomingMessage(mixed $event): void
    {
        $data = [];
        if (is_array($event)) {
            $data = $event;
        } elseif (is_object($event) && method_exists($event, 'getData')) {
            $data = $event->getData();
        } else {
            throw new InvalidArgumentException('Invalid $event data in handleIncomingMessage');
        }

        $messageData = $data['message'] ?? [];
        $conversationId = (int) ($data['conversation_id'] ?? $messageData['conversation_id'] ?? 0);

        if ($this->activeConversationId !== null && $conversationId === $this->activeConversationId) {
            $this->dispatch('message-received');

            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $user = Auth::user();
            if ($user) {
                $user->notifications()
                    ->where('data->type', 'chat_message')
                    ->where('data->conversation_id', (string) $conversationId)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);

                $this->dispatch('databaseNotificationsSent');
            }
        }

        $this->dispatch('$refresh');
    }

    /**
     * @param  array{conversationId?: int|string, senderId?: int|string}  $event
     */
    public function handleTypingEvent(array $event): void
    {
        $conversationId = (int) ($event['conversationId'] ?? 0);
        $senderId = (int) ($event['senderId'] ?? 0);

        if (
            $senderId !== Auth::id() &&
            $this->activeConversationId === $conversationId
        ) {
            $this->dispatch('user-typing');
        }
    }

    public function resolveConversation(int $id): void
    {
        $conversation = $this->participantConversationQuery()
            ->where('id', $id)
            ->where('conversation_type_id', ConversationType::SUPPORT_ID)
            ->where('conversation_status_id', ConversationStatus::OPEN_ID)
            ->first();

        if (! $conversation) {
            return;
        }

        $conversation
            ->update(['conversation_status_id' => ConversationStatus::CLOSED_ID]);

        if ($this->activeConversationId === $id) {
            $this->dispatch('$refresh');
        }
    }

    public function createSupportTicket(): void
    {
        $user = Auth::user();
        if (! $user || $user->user_type_id !== UserType::VENDOR_ID) {
            return;
        }

        $conversation = Conversation::where('conversation_type_id', ConversationType::SUPPORT_ID)
            ->where('conversation_status_id', ConversationStatus::OPEN_ID)
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        if (! $conversation) {
            $conversation = $this->createSupportConversation($user);
        }

        $this->conversationFilter = 'support_open';
        $this->selectConversation($conversation->id);
    }

    public function canCreateSupportTicket(): bool
    {
        $user = Auth::user();
        if (! $user || $user->user_type_id !== UserType::VENDOR_ID) {
            return false;
        }

        return ! Conversation::where('conversation_type_id', ConversationType::SUPPORT_ID)
            ->where('conversation_status_id', ConversationStatus::OPEN_ID)
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();
    }

    public function canResolveActiveConversation(): bool
    {
        $conversation = $this->activeConversation();

        return $conversation instanceof Conversation
            && (int) $conversation->conversation_type_id === ConversationType::SUPPORT_ID
            && (int) $conversation->conversation_status_id === ConversationStatus::OPEN_ID;
    }

    public function canSendInActiveConversation(): bool
    {
        $conversation = $this->activeConversation();

        return $conversation instanceof Conversation
            && ! $this->isResolvedSupportConversation($conversation);
    }

    /**
     * @return Collection<int, Message>
     */
    public function getActiveMessages(): Collection
    {
        if (! $this->activeConversationId) {
            return collect();
        }

        return Message::query()
            ->where('conversation_id', $this->activeConversationId)
            ->with('sender')
            ->oldest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.chat-inbox');
    }

    private function activeConversation(): ?Conversation
    {
        if (! $this->activeConversationId) {
            return null;
        }

        return $this->participantConversationQuery()
            ->with(['participants.user', 'type', 'status'])
            ->where('id', $this->activeConversationId)
            ->first();
    }

    /** @return Builder<Conversation> */
    private function participantConversationQuery(): Builder
    {
        $userId = Auth::id();

        return Conversation::query()
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
    }

    private function isResolvedSupportConversation(Conversation $conversation): bool
    {
        return (int) $conversation->conversation_type_id === ConversationType::SUPPORT_ID
            && (int) $conversation->conversation_status_id === ConversationStatus::CLOSED_ID;
    }

    private function createSupportConversation(User $user): Conversation
    {
        $conversation = Conversation::create([
            'conversation_type_id' => ConversationType::SUPPORT_ID,
            'conversation_status_id' => ConversationStatus::OPEN_ID,
        ]);

        $participants = [[
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]];

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

        return $conversation;
    }
}
