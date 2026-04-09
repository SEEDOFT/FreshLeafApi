<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\CreateChatSessionRequest;
use App\Http\Requests\Ai\FetchChatHistoryRequest;
use App\Http\Requests\Ai\StoreChatMessageRequest;
use App\Jobs\ProcessAiChatMessageJob;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function createSession(
        CreateChatSessionRequest $request,
    ): JsonResponse {
        $user = $request->user();
        $validated = $request->validated();

        $sessionId = $validated['session_id'] ?? (string) Str::uuid();

        $session = AiChatSession::query()->firstOrCreate(
            [
                'session_id' => $sessionId,
                'user_id' => $user->id,
            ],
            [
                'title' => $validated['title'] ?? null,
                'last_message_at' => now(),
            ],
        );

        return $this->successResponse(
            [
                'session_id' => $session->session_id,
                'title' => $session->title,
                'created_at' => optional(
                    $session->created_at,
                )->toIso8601String(),
                'updated_at' => optional(
                    $session->updated_at,
                )->toIso8601String(),
            ],
            'Chat session ready',
        );
    }

    public function storeMessage(StoreChatMessageRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $session = AiChatSession::where('session_id', $validated['session_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $session) {
            return $this->errorResponse('Chat session not found', 404);
        }

        $result = DB::transaction(function () use (
            $user,
            $session,
            $validated,
            $request,
        ): array {
            $userMessage = AiChatMessage::query()->create([
                'ai_chat_session_id' => $session->id,
                'user_id' => $user->id,
                'message_id' => (string) Str::uuid(),
                'role' => 'user',
                'content' => $validated['message'],
                'status' => 'done',
                'sequence' => 0,
            ]);

            $assistantMessage = AiChatMessage::query()->create([
                'ai_chat_session_id' => $session->id,
                'user_id' => $user->id,
                'message_id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => '',
                'status' => 'streaming',
                'sequence' => 1,
            ]);

            $session->update([
                'last_message_at' => now(),
            ]);

            ProcessAiChatMessageJob::dispatch(
                userId: $user->id,
                sessionId: $session->session_id,
                messageId: $assistantMessage->message_id,
                prompt: $validated['message'],
                language: $request->header('Accept-Language'),
                temperature: $validated['temperature'] ?? null,
                maxOutputTokens: $validated['max_output_tokens'] ?? null,
            );

            return [$userMessage, $assistantMessage];
        });

        return $this->successResponse(
            [
                'session_id' => $session->session_id,
                'user_message_id' => $result[0]->message_id,
                'ai_message_id' => $result[1]->message_id,
                'status' => 'queued',
            ],
            'Message accepted',
        );
    }

    public function history(FetchChatHistoryRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $session = AiChatSession::where('session_id', $validated['session_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $session) {
            return $this->errorResponse('Chat session not found', 404);
        }

        $limit = $validated['limit'] ?? 100;

        $messages = AiChatMessage::where('ai_chat_session_id', $session->id)
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->map(
                static fn (AiChatMessage $message): array => [
                    'session_id' => $session->session_id,
                    'message_id' => $message->message_id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'status' => $message->status,
                    'sequence' => $message->sequence,
                    'timestamp' => optional(
                        $message->created_at,
                    )->toIso8601String(),
                    'error' => $message->error,
                ],
            )
            ->values();

        return $this->successResponse(
            [
                'session_id' => $session->session_id,
                'messages' => $messages,
            ],
            'Chat history loaded',
        );
    }
}
