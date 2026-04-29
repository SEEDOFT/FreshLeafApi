<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Events\SupportMessageSent;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class SupportChat extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.support-chat';

    protected static ?string $slug = 'support-chat';

    protected string|Width|null $maxContentWidth = Width::Full;

    #[Url]
    public ?int $activeTicketId = null;

    public string $message = '';

    public function getHeading(): string
    {
        return __('admin.support.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.support.nav_label');
    }

    /**
     * @return Collection<int, SupportTicket>
     */
    public function getTickets(): Collection
    {
        return SupportTicket::with(['user'])
            ->where('status', 'open')
            ->latest('updated_at')
            ->get();
    }

    public function selectTicket(int $id): void
    {
        $this->activeTicketId = $id;
        $this->dispatch('ticket-selected');
    }

    public function sendMessage(): void
    {
        if (trim($this->message) === '' || ! $this->activeTicketId) {
            return;
        }

        $ticket = SupportTicket::findOrFail($this->activeTicketId);

        $msg = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_id' => auth()->id(),
            'message' => $this->message,
        ]);

        $ticket->touch();

        broadcast(new SupportMessageSent($msg))->toOthers();

        $this->message = '';
        $this->dispatch('message-sent');
    }

    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        $listeners = [
            'echo-private:support.admin,NewSupportTicket' => '$refresh',
        ];

        if ($this->activeTicketId) {
            $listeners["echo-private:support.ticket.{$this->activeTicketId},SupportMessageSent"] = 'handleIncomingMessage';
        }

        return $listeners;
    }

    /** @param array<string, mixed> $event */
    public function handleIncomingMessage(array $event): void
    {
        $this->dispatch('message-received');
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
