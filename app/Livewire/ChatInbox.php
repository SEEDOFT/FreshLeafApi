<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Events\ChatMessageSent;
use App\Events\ChatTyping;
use App\Models\Conversation;
use App\Models\ConversationStatus;
use App\Models\Message;
use App\Notifications\NewChatMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

    public bool $showHistory = true;

    protected const string FUNC_HANDLE_INCOMING_MESSAGE = 'handleIncomingMessage';

    protected const string FUNC_HANDLE_TYPING_EVENT = 'handleTypingEvent';

    public function mount(): void
    {
        $this->showHistory = (bool) session('chat_show_history', true);
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

        return Conversation::query()
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['participants.user', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->latest('updated_at')
            ->get();
    }

    public function selectConversation(int $id): void
    {
        $this->activeConversationId = $id;

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

        $conversation = Conversation::findOrFail($this->activeConversationId);

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

        $otherParticipants = $conversation->participants()->where('user_id', '!=', Auth::id())->with('user')->get();
        foreach ($otherParticipants as $participant) {
            if ($participant->user) {
                $participant->user->notify(new NewChatMessage($msg));
            }
        }

        $this->message = '';
        $this->file = null;
        $this->dispatch('message-sent');
    }

    public function sendTyping(): void
    {
        if ($this->activeConversationId) {
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

        // The incoming event payload usually contains the serialized model.
        // In Laravel echo, it's passed as an array under the property name 'message'
        $messageData = $data['message'] ?? [];
        $conversationId = (int) ($messageData['conversation_id'] ?? 0);

        if ($this->activeConversationId !== null && $conversationId === $this->activeConversationId) {
            $this->dispatch('message-received');

            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
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
        Conversation::where('id', $id)
            ->update(['conversation_status_id' => ConversationStatus::CLOSED_ID]);
        if ($this->activeConversationId === $id) {
            $this->activeConversationId = null;
        }
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
}
