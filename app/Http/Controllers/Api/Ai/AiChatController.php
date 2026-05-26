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
use App\Models\UserType;
use App\Services\Ai\AiService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function __construct(private AiService $aiService) {}

    private function getRoleRelation(int $userTypeId): string
    {
        return match ($userTypeId) {
            UserType::ADMIN_ID => 'admin',
            UserType::VENDOR_ID => 'vendor',
            default => 'consumer',
        };
    }

    /**
     * Create or get a chat session.
     */
    public function createSession(CreateChatSessionRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);

        $sessionId = $validatedData['session_id'] ?? (string) Str::uuid();

        $session = AiChatSession::where('session_id', $sessionId)
            ->whereHas(
                $this->getRoleRelation($user->user_type_id),
                static fn (Builder $query) => $query->where('users.id', $user->id)
            )
            ->first();

        if (! $session) {
            $session = AiChatSession::create([
                'session_id' => $sessionId,
                'user_id' => $user->id,
                'title' => $validatedData['title'] ?? null,
                'last_message_at' => Carbon::now(),
            ]);
        }

        return static::successResponse([
            'session_id' => $session->session_id,
            'title' => $session->title,
            'created_at' => $session->created_at?->toIso8601String(),
            'updated_at' => $session->updated_at?->toIso8601String(),
        ], __('api.ai_chat.chat_started'));
    }

    /**
     * Store a new chat message and dispatch processing job.
     */
    public function storeMessage(StoreChatMessageRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);

        $session = AiChatSession::where('session_id', $validatedData['session_id'])
            ->whereHas($this->getRoleRelation($user->user_type_id), fn ($query) => $query->where('users.id', $user->id))
            ->first();

        if (! $session) {
            return static::notFoundResponse(__('api.ai_chat.session_not_found'));
        }

        try {
            $this->aiService->assertAvailable();
        } catch (Exception $exception) {
            return static::errorResponse(
                $this->aiService->normalizeFailureMessage($exception),
                503
            );
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
                    'last_message_at' => Carbon::now(),
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
        ], __('api.ai_chat.response_received'));
    }

    /**
     * Fetch chat history for a session.
     */
    public function history(FetchChatHistoryRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $this->authenticatedUser($request);

        $session = AiChatSession::where('session_id', $validatedData['session_id'])
            ->whereHas($this->getRoleRelation($user->user_type_id), fn ($query) => $query->where('users.id', $user->id))
            ->first();

        if (! $session) {
            return static::notFoundResponse(__('api.ai_chat.session_not_found'));
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
                'timestamp' => $message->created_at?->toIso8601String(),
                'error' => $message->error,
            ])
            ->values();

        return static::successResponse([
            'session_id' => $session->session_id,
            'messages' => $messages,
        ], __('api.ai_chat.history_retrieved'));
    }
}
