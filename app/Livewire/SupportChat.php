<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Events\SupportMessageSent;
use App\Events\SupportTyping;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Notifications\NewSupportMessageNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

use function broadcast;
use function is_array;
use function is_object;
use function method_exists;
use function trim;

class SupportChat extends Component
{
    use WithFileUploads;

    #[Url]
    public ?int $activeTicketId = null;

    public string $message = '';

    /** @var mixed */
    public $file;

    public bool $showHistory = true;

    // Listeners Methods

    protected const string FUNC_HANDLE_INCOMING_MESSAGE = 'handleIncomingMessage';

    protected const string FUNC_HANDLE_TYPING_EVENT = 'handleTypingEvent';

    /**
     * Mount the component
     */
    public function mount(): void
    {
        $this->showHistory = (bool) session('support_chat_show_history', true);
    }

    /**
     * Updated Show History
     */
    public function updatedShowHistory(bool $value): void
    {
        session(['support_chat_show_history' => $value]);
    }

    /**
     * Toggle History
     */
    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
        session(['support_chat_show_history' => $this->showHistory]);
    }

    /**
     * Get all active ticket.
     *
     * @return Collection<int, SupportTicket>
     */
    public function getTickets(): Collection
    {
        return SupportTicket::with(['latestMessage', 'user'])
            ->where('status', SupportTicket::OPEN)
            ->latest('updated_at')
            ->get();
    }

    /**
     * Select specific ticket and mark as read.
     */
    public function selectTicket(int $id): void
    {
        $this->activeTicketId = $id;

        SupportMessage::where('support_ticket_id', $id)
            ->where('sender_type', SupportMessage::USER)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->dispatch('ticket-selected');
    }

    /**
     * Send Messages
     */
    public function sendMessage(): void
    {
        if (trim($this->message) === '' && ! $this->file) {
            return;
        }

        if (! $this->activeTicketId) {
            return;
        }

        $ticket = SupportTicket::findOrFail($this->activeTicketId);

        $filePath = null;

        if ($this->file) {
            $filePath = $this->file->store('support/files', 'public');
        }

        $msg = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => SupportMessage::ADMIN,
            'sender_id' => Auth::id(),
            'message' => trim($this->message),
            'file_path' => $filePath,
        ]);

        $ticket->touch();

        broadcast(new SupportMessageSent($msg))->toOthers();

        Notification::sendNow(
            $ticket->user,
            new NewSupportMessageNotification($msg)
        );

        $this->message = '';
        $this->file = null;
        $this->dispatch('message-sent');
    }

    /**
     * Send typing
     */
    public function sendTyping(): void
    {
        if ($this->activeTicketId) {
            broadcast(
                new SupportTyping(
                    $this->activeTicketId,
                    SupportMessage::ADMIN
                )
            )->toOthers();
        }
    }

    /**
     * Get Listeners
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            'echo-private:support.admin,NewSupportTicket' => '$refresh',
            'echo-private:support.admin,SupportMessageSent' => self::FUNC_HANDLE_INCOMING_MESSAGE,
            'echo-private:support.admin,SupportTyping' => self::FUNC_HANDLE_TYPING_EVENT,
        ];
    }

    /**
     * Handle the incomming message
     */
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

        $ticketId = (int) ($data['support_ticket_id'] ?? 0);
        $isActiveTicket = $this->activeTicketId !== null
            && $ticketId === $this->activeTicketId;

        if ($isActiveTicket) {
            $this->dispatch('message-received');

            SupportMessage::where('support_ticket_id', $ticketId)
                ->where('sender_type', SupportMessage::USER)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $this->dispatch('$refresh');
    }

    /**
     * Handle typing event from WebSocket.
     *
     * @param  array{ticket_id?: int|string, sender_type?: string}  $event
     */
    public function handleTypingEvent(array $event): void
    {
        $ticketId = (int) ($event['ticket_id'] ?? 0);
        $senderType = $event['sender_type'] ?? '';

        if (
            $senderType === SupportMessage::USER &&
            $this->activeTicketId === $ticketId
        ) {
            $this->dispatch('user-typing');
        }
    }

    /**
     * Resolve ticket.
     */
    public function resolveTicket(int $id): void
    {
        SupportTicket::where('id', $id)
            ->update(['status' => SupportTicket::RESOLVED]);
        if ($this->activeTicketId === $id) {
            $this->activeTicketId = null;
        }
    }

    /**
     * Get active message
     *
     * @return Collection<int, SupportMessage>
     */
    public function getActiveMessages(): Collection
    {
        if (! $this->activeTicketId) {
            return collect();
        }

        return SupportMessage::query()
            ->where('support_ticket_id', $this->activeTicketId)
            ->oldest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.support-chat');
    }
}
