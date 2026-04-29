<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Events\NewSupportTicket;
use App\Events\SupportMessageSent;
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
     * Send a message in the support ticket.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => ['required', 'exists:support_tickets,id'],
            'message' => ['required', 'string'],
        ]);

        $user = $request->user();
        $ticket = SupportTicket::findOrFail($request->ticket_id);

        // Ensure user owns the ticket
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'status' => [
                    'code' => '403',
                    'success' => false,
                    'message' => 'Unauthorized access to ticket',
                ],
                'data' => [],
            ], 403);
        }

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        broadcast(new SupportMessageSent($message))->toOthers();

        // Notify admins
        $admins = User::where('user_type_id', UserType::ADMIN)->get();
        Notification::send($admins, new NewSupportMessageNotification($message));

        return response()->json([
            'status' => [
                'code' => '201',
                'success' => true,
                'message' => 'Message sent',
            ],
            'data' => $message,
        ], 201);
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
}
