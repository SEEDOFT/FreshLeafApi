<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Constants\StorageDirectory;
use App\Events\NewSupportTicket;
use App\Events\SupportMessageSent;
use App\Events\SupportTyping;
use App\Filament\Admin\Pages\SupportChat;
use App\Http\Controllers\Controller;
use App\Http\Resources\SupportChat\SupportTicketResource;
use App\Http\Resources\User\SupportMessageResource;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Notifications\NewSupportMessageNotification;
use App\Notifications\NewSupportTicketNotification;
use Filament\Actions\Action;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

use function broadcast;

class SupportChatController extends Controller
{
    /**
     * Get all support tickets for the user.
     */
    public function getTickets(Request $request): JsonResponse
    {
        $tickets = SupportTicket::query()
            ->where('user_id', $this->authenticatedUser($request)->id)
            ->latest()
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse(
            SupportTicketResource::collection($tickets),
            __('api.support_chat.tickets_retrieved')
        );
    }

    /**
     * Get the active support ticket for the user.
     */
    public function getActiveTicket(Request $request): JsonResponse
    {
        $ticket = SupportTicket::query()
            ->where('user_id', $this->authenticatedUser($request)->id)
            ->where('status', SupportTicket::OPEN)
            ->latest()
            ->first();

        if (! $ticket) {
            abort(404, __('api.support_chat.no_active_ticket'));
        }

        return static::successResponse(
            new SupportTicketResource($ticket),
            __('api.support_chat.session_retrieved'));
    }

    /**
     * Create a new support ticket.
     */
    public function createTicket(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $ticket = SupportTicket::query()
            ->where('user_id', $user->id)
            ->where('status', SupportTicket::OPEN)
            ->first();

        if (! $ticket) {
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'status' => SupportTicket::OPEN,
            ]);

            broadcast(new NewSupportTicket($ticket))->toOthers();

            $admins = User::where('user_type_id', UserType::ADMIN_ID)
                ->where('user_status_id', UserStatus::ACTIVE_ID)
                ->get();

            Notification::send($admins, new NewSupportTicketNotification($ticket));

            if ($admins->isNotEmpty()) {
                \Filament\Notifications\Notification::make()
                    ->title(__('api.notifications.new_support_ticket_title'))
                    ->body(__('api.notifications.new_support_ticket_body'))
                    ->icon('heroicon-o-ticket')
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->label(__('api.support_chat.view_chat'))
                            ->button()
                            ->url(SupportChat::getUrl()),
                    ])
                    ->sendToDatabase($admins);
            }
        }

        return static::successResponse(
            new SupportTicketResource($ticket),
            __('api.support_chat.session_created')
        );
    }

    /**
     * Broadcast that the user is typing.
     */
    public function sendTyping(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'ticket_id' => ['required', 'exists:support_tickets,id'],
        ]);

        broadcast(
            new SupportTyping(
                (int) $validatedData['ticket_id'], SupportMessage::USER
            )
        )->toOthers();

        return static::successResponse(message: __('api.support_chat.typing'));
    }

    /**
     * Send a message in the support ticket, supporting optional file upload.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $validatedData = $request->validate([
            'ticket_id' => ['required', 'integer', 'exists:support_tickets,id'],
            'message' => ['required', 'string', 'max:1200'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:png,jpg,jpeg,pdf'],
        ]);

        $filePath = null;

        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')
                ->store(StorageDirectory::SUPPORT_MESSAGES, 'public');
        }

        $ticket = SupportTicket::find((int) $validatedData['ticket_id']);

        if (! $ticket) {
            abort(404, __('api.support_chat.ticket_not_found'));
        }

        if ($ticket->user_id !== $user->id) {
            abort(401, __('api.support_chat.unauthorized_access'));
        }

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'message' => $validatedData['message'],
            'file_path' => $filePath,
        ]);

        $ticket->touch();

        broadcast(new SupportMessageSent($message))->toOthers();

        $admins = User::where('user_type_id', UserType::ADMIN_ID)
            ->where('user_status_id', UserStatus::ACTIVE_ID)
            ->get();

        Notification::send($admins, new NewSupportMessageNotification($message));

        if ($admins->isNotEmpty()) {
            \Filament\Notifications\Notification::make()
                ->title(__('api.notifications.new_support_message_title'))
                ->body(Str::limit($message->message, 50))
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->success()
                ->actions([
                    Action::make('view')
                        ->label(__('api.support_chat.view_chat'))
                        ->button()
                        ->url(SupportChat::getUrl()),
                ])
                ->sendToDatabase($admins);
        }

        return static::successResponse(
            new SupportMessageResource($message),
            __('api.support_chat.message_sent')
        );
    }

    /**
     * Get message history for a ticket.
     */
    public function getMessages(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $validatedData = $request->validate([
            'ticket_id' => ['required', 'integer', 'exists:support_tickets,id'],
        ]);

        $ticket = SupportTicket::find((int) $validatedData['ticket_id']);

        if (! $ticket) {
            abort(404, __('api.support_chat.ticket_not_found'));
        }

        if ($ticket->user_id !== $user->id) {
            abort(401, __('api.support_chat.unauthorized_access'));
        }

        $ticket->messages()
            ->where('sender_type', SupportMessage::ADMIN)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        $messages = $ticket->messages()->oldest()->get();

        return static::successResponse(
            SupportMessageResource::collection($messages),
            __('api.support_chat.messages_retrieved')
        );
    }

    /**
     * Get unread message count.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $ticket = SupportTicket::where('user_id', $user->id)
            ->where('status', SupportTicket::OPEN)
            ->first();

        if (! $ticket) {
            return static::successResponse([
                'count' => 0,
            ], __('api.support_chat.no_unread'));
        }

        $count = $ticket->messages()
            ->where('sender_type', SupportMessage::ADMIN)
            ->where('is_read', false)
            ->count();

        return static::successResponse([
            'count' => $count,
        ], __('api.support_chat.unread_retrieved'));
    }
}
