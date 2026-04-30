<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Events\NewSupportTicket;
use App\Events\SupportMessageSent;
use App\Events\SupportTyping;
use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
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
        $user = $request->user();

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
            $admins = User::where('user_type_id', UserType::ADMIN)->get();
            Notification::send($admins, new NewSupportTicketNotification($ticket));
        }

        return response()->json([
            'status' => [
                'code' => '200',
                'success' => true,
                'message' => 'Active ticket retrieved',
            ],
            'data' => [
                'id' => $ticket->id,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Broadcast that the user is typing.
     */
    public function sendTyping(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => ['required', 'exists:support_tickets,id'],
        ]);

        broadcast(new SupportTyping((int) $request->ticket_id, 'user'))->toOthers();

        return response()->json(['status' => 'success']);
    }

    /**
     * Send a message in the support ticket, supporting optional file upload.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => ['required', 'exists:support_tickets,id'],
            'message' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:5120'],
        ]);

        $user = $request->user();
        $ticket = SupportTicket::findOrFail($request->ticket_id);

        if ($ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('support/files', 'public');
        }

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'message' => $request->message ?? '',
            'file_path' => $filePath,
        ]);

        broadcast(new SupportMessageSent($message))->toOthers();

        // Notify admins
        $admins = User::where('user_type_id', UserType::ADMIN)->get();
        Notification::send($admins, new NewSupportMessageNotification($message));

        return response()->json(['data' => $message], 201);
    }

    /**
     * Get message history for a ticket.
     */
    public function getMessages(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => ['required', 'exists:support_tickets,id'],
        ]);

        $user = $request->user();
        $ticket = SupportTicket::findOrFail($request->ticket_id);

        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'status' => [
                    'code' => '403',
                    'success' => false,
                    'message' => 'Unauthorized access to ticket history',
                ],
                'data' => [],
            ], 403);
        }

        // Mark admin messages as read
        $ticket->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $ticket->messages()->oldest()->get();

        return response()->json([
            'status' => [
                'code' => '200',
                'success' => true,
                'message' => 'Messages retrieved',
            ],
            'data' => $messages,
        ]);
    }

    /**
     * Get unread message count.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $ticket = SupportTicket::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (! $ticket) {
            return response()->json(['count' => 0]);
        }

        $count = $ticket->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
