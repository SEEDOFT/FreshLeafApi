<?php

declare(strict_types=1);

namespace App\Channels;

use App\Events\ChatNotificationSent;
use App\Models\Notification as NotificationModel;
use App\Models\NotificationStatus;
use App\Models\NotificationType;
use App\Models\User;
use App\Models\UserType;
use App\Notifications\PushNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! $notification instanceof PushNotification) {
            return;
        }

        $typeId = NotificationType::SYSTEM_ID;
        $typeCode = $notification->data['type'] ?? '';

        if ($typeCode === 'order_status_update' || $typeCode === 'new_order') {
            $typeId = NotificationType::ORDER_UPDATE_ID;
        } elseif ($typeCode === 'promotion') {
            $typeId = NotificationType::PROMOTION_ID;
        }

        NotificationModel::create([
            'user_id' => $notifiable->id,
            'notification_type_id' => $typeId,
            'notification_status_id' => NotificationStatus::UNREAD_ID,
            'title' => $notification->title,
            'message' => $notification->body,
            'data' => $notification->data,
        ]);

        if (
            $notifiable instanceof User &&
            (
                $notifiable->isType(UserType::ADMIN_ID) ||
                $notifiable->isType(UserType::VENDOR_ID)
            )
        ) {
            $filamentNotification = FilamentNotification::make()
                ->title($notification->title)
                ->body($notification->body)
                ->info();

            $url = $this->filamentDeepLinkFor($notifiable, $notification);
            if ($url !== null) {
                $filamentNotification->actions([
                    Action::make('open')
                        ->label('Open chat')
                        ->url($url)
                        ->button(),
                ]);
            }

            $filamentNotification->sendToDatabase($notifiable, true);

            if (($notification->data['type'] ?? null) === 'chat_message') {
                broadcast(new ChatNotificationSent(
                    user: $notifiable,
                    title: $notification->title,
                    body: $notification->body,
                    data: $notification->data,
                ));
            } else {
                $filamentNotification->broadcast($notifiable);
            }
        }
    }

    private function filamentDeepLinkFor(User $notifiable, PushNotification $notification): ?string
    {
        if (($notification->data['type'] ?? null) !== 'chat_message') {
            return null;
        }

        $conversationId = $notification->data['conversation_id'] ?? null;
        if (! is_numeric($conversationId)) {
            return null;
        }

        $panelPath = $notifiable->isType(UserType::VENDOR_ID)
            ? '/vendor/support-chat'
            : '/admin/support-chat';

        return url($panelPath.'?activeConversationId='.(int) $conversationId);
    }
}
