<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Events\SupportMessageSent;
use App\Events\SupportTyping;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Notifications\NewSupportMessageNotification;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;
use Override;

use function auth;
use function gettype;
use function is_array;
use function is_object;
use function method_exists;
use function trim;

class SupportChat extends Page
{
    use WithFileUploads;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    #[Override]
    protected string $view = 'filament.pages.support-chat';

    #[Override]
    protected static ?string $slug = 'support-chat';

    #[Override]
    protected string|Width|null $maxContentWidth = Width::Full;

    #[Url]
    public ?int $activeTicketId = null;

    public string $message = '';

    /** @var mixed */
    public $file;

    #[Override]
    public function getHeading(): string
    {
        return __('admin.support.title');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('admin.support.nav_label');
    }

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $unreadCount = SupportMessage::where('sender_type', 'user')
            ->where('is_read', false)
            ->count();

        return $unreadCount > 0 ? (string) $unreadCount : null;
    }

    /**
     * @return Collection<int, SupportTicket>
     */
    public function getTickets(): Collection
    {
        return SupportTicket::with(['latestMessage', 'user'])
            ->where('status', 'open')
            ->latest('updated_at')
            ->get();
    }

    public function selectTicket(int $id): void
    {
        $this->activeTicketId = $id;

        // Mark messages as read
        SupportMessage::where('support_ticket_id', $id)
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->dispatch('ticket-selected');
    }

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
            'sender_type' => 'admin',
            'sender_id' => auth()->id(),
            'message' => trim($this->message),
            'file_path' => $filePath,
        ]);

        $ticket->touch();

        broadcast(new SupportMessageSent($msg))->toOthers();

        // Notify user
        Log::info('Sending notification to user', [
            'user_id' => $ticket->user->id,
            'user_email' => $ticket->user->email,
            'message_id' => $msg->id,
        ]);

        Notification::sendNow($ticket->user, new NewSupportMessageNotification($msg));

        Log::info('Notification sent successfully', ['user_id' => $ticket->user->id]);

        $this->message = '';
        $this->file = null;
        $this->dispatch('message-sent');
    }

    public function sendTyping(): void
    {
        if ($this->activeTicketId) {
            broadcast(new SupportTyping($this->activeTicketId, 'admin'))->toOthers();
        }
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getListeners(): array
    {
        Log::info('SupportChat: Setting up echo listeners', [
            'user_id' => auth()->id(),
            'user_type' => auth()->user()?->user_type_id,
            'listeners' => [
                'echo-private:support.admin,NewSupportTicket' => '$refresh',
                'echo-private:support.admin,SupportMessageSent' => 'handleIncomingMessage',
                'echo-private:support.admin,SupportTyping' => 'handleTypingEvent',
            ],
        ]);

        return [
            'echo-private:support.admin,NewSupportTicket' => '$refresh',
            'echo-private:support.admin,SupportMessageSent' => 'handleIncomingMessage',
            'echo-private:support.admin,SupportTyping' => 'handleTypingEvent',
        ];
    }

    public function handleIncomingMessage(mixed $event): void
    {
        Log::info('SupportChat: handleIncomingMessage called', [
            'event_type' => gettype($event),
            'active_ticket_id' => $this->activeTicketId,
        ]);

        $data = is_array($event) ? $event : [];

        if (is_object($event) && method_exists($event, 'getData')) {
            $data = $event->getData();
        }

        $ticketId = (int) ($data['support_ticket_id'] ?? 0);
        $isActiveTicket = $this->activeTicketId !== null
            && $ticketId === $this->activeTicketId;

        if ($isActiveTicket) {
            $this->dispatch('message-received');

            SupportMessage::where('support_ticket_id', $ticketId)
                ->where('sender_type', 'user')
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
        Log::info('SupportChat: handleTypingEvent called', [
            'event' => $event,
            'active_ticket_id' => $this->activeTicketId,
        ]);

        $ticketId = (int) ($event['ticket_id'] ?? 0);
        $senderType = $event['sender_type'] ?? '';

        if ($senderType === 'user' && $this->activeTicketId === $ticketId) {
            $this->dispatch('user-typing');
        }
    }

    public function resolveTicket(int $id): void
    {
        SupportTicket::where('id', $id)->update(['status' => 'resolved']);
        if ($this->activeTicketId === $id) {
            $this->activeTicketId = null;
        }
    }

    /** @return Collection<int, SupportMessage> */
    public function getActiveMessages(): Collection
    {
        if (! $this->activeTicketId) {
            return collect();
        }

        return SupportMessage::where('support_ticket_id', $this->activeTicketId)
            ->oldest()
            ->get();
    }
}
