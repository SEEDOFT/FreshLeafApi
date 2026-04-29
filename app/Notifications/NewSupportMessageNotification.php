<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Filament\Pages\SupportChat;
use App\Models\SupportMessage;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewSupportMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public SupportMessage $supportMessage
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New Support Message')
            ->body("New message from {$this->supportMessage->ticket->user->fullName}: ".Str::limit($this->supportMessage->message, 50))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label('View Chat')
                    ->url(fn () => SupportChat::getUrl(['activeTicketId' => $this->supportMessage->support_ticket_id])),
            ])
            ->getDatabaseMessage();
    }
}
