<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Events\NewSupportTicket;
use App\Events\SupportMessageSent;
use App\Events\SupportTyping;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\SupportMessageResource;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Notifications\NewSupportMessageNotification;
use App\Notifications\NewSupportTicketNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SupportChatController extends Controller
{
    /**
     * Get or create the active support ticket for the user.
     */
    public function getActiveTicket(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $ticket = SupportTicket::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (! $ticket) {
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'status' => 'open',
            ]);

            broadcast(new NewSupportTicket($ticket))->toOthers();

            // Notify admins
            $admins = User::where('user_type_id', UserType::ADMIN)
                ->where('user_status_id', UserStatus::ACTIVE)
                ->get();
            Notification::send($admins, new NewSupportTicketNotification($ticket));
        }

        return static::successResponse([
            'id' => $ticket->id,
            'status' => $ticket->status,
            'created_at' => $ticket->created_at?->toIso8601String(),
        ], 'Active ticket retrieved');
    }

    /**
     * Broadcast that the user is typing.
     */
    public function sendTyping(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'ticket_id' => ['required', 'exists:support_tickets,id'],
        ]);

        broadcast(new SupportTyping((int) $validatedData['ticket_id'], 'user'))->toOthers();

        return static::successResponse(message: 'Typing indicator sent');
    }

    /**
     * Send a message in the support ticket, supporting optional file upload.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'ticket_id' => ['required', 'string', 'exists:support_tickets,id'],
            'message' => ['nullable', 'string', 'max:1200'],
            'file' => ['nullable', 'file', 'max:5120'],
        ]);

        $user = $this->authenticatedUser($request);
        $ticket = SupportTicket::findOrFail((int) $validatedData['ticket_id']);

        if ($ticket->user_id !== $user->id) {
            return static::unauthorizedResponse('Unauthorized access to ticket history');
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('support/files', 'public');
        }

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'message' => $validatedData['message'] ?? '',
            'file_path' => $filePath,
        ]);

        $ticket->touch();

        broadcast(new SupportMessageSent($message))->toOthers();

        // Notify admins
        $admins = User::where('user_type_id', UserType::ADMIN)
            ->where('user_status_id', UserStatus::ACTIVE)
            ->get();
        Notification::send($admins, new NewSupportMessageNotification($message));

        return static::successResponse(
            new SupportMessageResource($message),
            'Message sent',
        );
    }

    /**
     * Get message history for a ticket.
     */
    public function getMessages(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'ticket_id' => ['required', 'string', 'exists:support_tickets,id'],
        ]);

        $user = $this->authenticatedUser($request);
        $ticket = SupportTicket::findOrFail((int) $validatedData['ticket_id']);

        if ($ticket->user_id !== $user->id) {
            return static::unauthorizedResponse('Unauthorized access to ticket history');
        }

        // Mark admin messages as read
        $ticket->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $ticket->messages()->oldest()->get();

        return static::successResponse(
            SupportMessageResource::collection($messages),
            'Messages retrieved',
        );
    }

    /**
     * Get unread message count.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $ticket = SupportTicket::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (! $ticket) {
            return static::successResponse(['count' => 0], 'No unread messages');
        }

        $count = $ticket->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->count();

        return static::successResponse(['count' => $count], 'Unread count retrieved');
    }
}
