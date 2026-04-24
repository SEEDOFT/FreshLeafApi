<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Ai;

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
    /**
     * Create or get a chat session.
     */
    public function createSession(CreateChatSessionRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $sessionId = $validatedData['session_id'] ?? (string) Str::uuid();
        $user = $this->authenticatedUser($request);

        $session = AiChatSession::firstOrCreate([
            'session_id' => $sessionId,
            'user_id' => $user->id,
        ], [
            'title' => $validatedData['title'] ?? null,
            'last_message_at' => \now(),
        ]);

        return static::successResponse([
            'session_id' => $session->session_id,
            'title' => $session->title,
            'created_at' => \optional($session->created_at)
                ->toIso8601String(),
            'updated_at' => \optional($session->updated_at)
                ->toIso8601String(),
        ], 'Chat session ready');
    }

    /**
     * Store a new chat message and dispatch processing job.
     */
    public function storeMessage(StoreChatMessageRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validated();

        $session = AiChatSession::where('session_id', $validatedData['session_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $session) {
            return static::errorResponse('Chat session not found', 404);
        }

        /** @var array{0: AiChatMessage, 1: AiChatMessage} */
        $result = DB::transaction(
            static function () use ($user, $session, $validatedData, $request): array {
                $userMessage = AiChatMessage::create([
                    'ai_chat_session_id' => $session->id,
                    'user_id' => $user->id,
                    'message_id' => (string) Str::uuid(),
                    'role' => 'user',
                    'content' => $validatedData['message'],
                    'status' => 'done',
                    'sequence' => 0,
                ]);

                $assistantMessage = AiChatMessage::create([
                    'ai_chat_session_id' => $session->id,
                    'user_id' => $user->id,
                    'message_id' => (string) Str::uuid(),
                    'role' => 'assistant',
                    'content' => '',
                    'status' => 'streaming',
                    'sequence' => 1,
                ]);

                $session->update([
                    'last_message_at' => \now(),
                ]);

                ProcessAiChatMessageJob::dispatch(
                    userId: $user->id,
                    sessionId: $session->session_id,
                    messageId: $assistantMessage->message_id,
                    prompt: $validatedData['message'],
                    language: $request->header('Accept-Language'),
                    temperature: $validatedData['temperature'] ?? null,
                    maxOutputTokens: $validatedData['max_output_tokens'] ?? null,
                );

                return [$userMessage, $assistantMessage];
            });

        return static::successResponse([
            'session_id' => $session->session_id,
            'user_message_id' => $result[0]->message_id,
            'ai_message_id' => $result[1]->message_id,
            'status' => 'queued',
        ], 'Message accepted');
    }

    /**
     * Fetch chat history for a session.
     */
    public function history(FetchChatHistoryRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validated();

        $session = AiChatSession::where('session_id', $validatedData['session_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $session) {
            return static::errorResponse('Chat session not found', 404);
        }

        $limit = $validatedData['limit'] ?? 100;

        $messages = AiChatMessage::where('ai_chat_session_id', $session->id)
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->map(static fn (AiChatMessage $message): array => [
                'session_id' => $session->session_id,
                'message_id' => $message->message_id,
                'role' => $message->role,
                'content' => $message->content,
                'status' => $message->status,
                'sequence' => $message->sequence,
                'timestamp' => \optional($message->created_at)
                    ->toIso8601String(),
                'error' => $message->error,
            ])
            ->values();

        return static::successResponse([
            'session_id' => $session->session_id,
            'messages' => $messages,
        ], 'Chat history loaded');
    }
}
