<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\AiMessageChunk;
use App\Events\AiMessageCompleted;
use App\Events\AiMessageFailed;
use App\Events\AiMessageStarted;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Services\AiService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessAiChatMessageJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 3;

    public int $timeout = 120;

    public int $backoff = 30;

    public function __construct(
        public int $userId,
        public string $sessionId,
        public string $messageId,
        public string $prompt,
        public ?string $language = null,
        public ?float $temperature = null,
        public ?int $maxOutputTokens = null,
    ) {
        $this->onQueue('ai-stream');
    }

    public function handle(AiService $aiService): void
    {
        $session = AiChatSession::where('session_id', $this->sessionId)
            ->where('user_id', $this->userId)
            ->first();

        $assistantMessage = AiChatMessage::where('message_id', $this->messageId)
            ->where('ai_chat_session_id', $session?->id)
            ->first();

        if (! $session || ! $assistantMessage) {
            Log::warning('AI chat session or message not found', [
                'session_id' => $this->sessionId,
                'message_id' => $this->messageId,
                'user_id' => $this->userId,
            ]);

            $this->broadcastFailure('Session or message not found');

            return;
        }

        $sequence = max(1, (int) $assistantMessage->sequence);
        $timestamp = now()->toIso8601String();

        event(new AiMessageStarted(
            userId: $this->userId,
            sessionId: $this->sessionId,
            messageId: $this->messageId,
            role: 'assistant',
            sequence: $sequence,
            timestamp: $timestamp,
        ));

        try {
            $history = AiChatMessage::where('ai_chat_session_id', $session->id)
                ->where('id', '<', $assistantMessage->id)
                ->whereIn('role', ['user', 'assistant'])
                ->orderBy('id')
                ->get(['role', 'content'])
                ->map(static fn (AiChatMessage $message): array => [
                    'role' => $message->role === 'assistant' ? 'assistant' : 'user',
                    'content' => $message->content,
                ])
                ->all();

            $lastHistoryMessage = end($history);

            if (
                is_array($lastHistoryMessage)
                && ($lastHistoryMessage['role'] ?? null) === 'user'
                && ($lastHistoryMessage['content'] ?? null) === $this->prompt
            ) {
                array_pop($history);
            }

            $options = [
                'temperature' => $this->temperature ?? 0.7,
                'maxOutputTokens' => $this->maxOutputTokens ?? 4096,
            ];

            $systemPrompt = $this->buildSystemPrompt();

            $fullText = $aiService->generateContentWithSystemPromptAndHistory(
                systemPrompt: $systemPrompt,
                history: $history,
                prompt: $this->prompt,
                options: $options,
            );

            $chunks = $this->chunkText($fullText);

            foreach ($chunks as $chunk) {
                $sequence++;

                event(new AiMessageChunk(
                    userId: $this->userId,
                    sessionId: $this->sessionId,
                    messageId: $this->messageId,
                    role: 'assistant',
                    textChunk: $chunk,
                    sequence: $sequence,
                    timestamp: now()->toIso8601String(),
                ));
            }

            $assistantMessage->update([
                'content' => $fullText,
                'status' => 'done',
                'sequence' => $sequence + 1,
                'error' => null,
            ]);

            $session->update(['last_message_at' => Carbon::now()]);

            event(new AiMessageCompleted(
                userId: $this->userId,
                sessionId: $this->sessionId,
                messageId: $this->messageId,
                role: 'assistant',
                fullText: $fullText,
                sequence: $sequence + 1,
                timestamp: now()->toIso8601String(),
            ));
        } catch (Exception $exception) {
            $friendlyMessage = $this->toClientSafeError($exception->getMessage());

            $assistantMessage->update([
                'status' => 'failed',
                'error' => $friendlyMessage,
                'sequence' => $sequence + 1,
            ]);

            event(new AiMessageFailed(
                userId: $this->userId,
                sessionId: $this->sessionId,
                messageId: $this->messageId,
                role: 'assistant',
                error: $friendlyMessage,
                sequence: $sequence + 1,
                timestamp: now()->toIso8601String(),
            ));

            Log::error('AI chat generation failed', [
                'user_id' => $this->userId,
                'session_id' => $this->sessionId,
                'message_id' => $this->messageId,
                'error' => $exception->getMessage(),
                'client_error' => $friendlyMessage,
                'attempt' => $this->attempts(),
            ]);

            throw $exception;
        }
    }

    public function failed(?Exception $exception): void
    {
        Log::critical('AI chat job permanently failed', [
            'user_id' => $this->userId,
            'session_id' => $this->sessionId,
            'message_id' => $this->messageId,
            'attempts' => $this->attempts(),
            'exception' => $exception?->getMessage(),
        ]);

        $this->broadcastFailure(
            $exception?->getMessage() ?? 'Job failed after max retries'
        );
    }

    private function broadcastFailure(string $error): void
    {
        try {
            $session = AiChatSession::where('session_id', $this->sessionId)
                ->where('user_id', $this->userId)
                ->first();

            $assistantMessage = AiChatMessage::where('message_id', $this->messageId)
                ->where('ai_chat_session_id', $session?->id)
                ->first();

            $sequence = $assistantMessage ? (int) $assistantMessage->sequence + 1 : 1;

            event(new AiMessageFailed(
                userId: $this->userId,
                sessionId: $this->sessionId,
                messageId: $this->messageId,
                role: 'assistant',
                error: 'AI service is temporarily unavailable. Please try again.',
                sequence: $sequence,
                timestamp: now()->toIso8601String(),
            ));
        } catch (Exception $e) {
            Log::error('Failed to broadcast failure event', [
                'session_id' => $this->sessionId,
                'message_id' => $this->messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function toClientSafeError(string $message): string
    {
        $normalized = mb_strtolower($message);

        if (str_contains($normalized, 'quota exceeded') || str_contains($normalized, '429')) {
            return 'AI service is temporarily busy. Please try again in about a minute.';
        }

        if (str_contains($normalized, 'api key') || str_contains($normalized, 'permission denied') || str_contains($normalized, '403')) {
            return 'AI service is not available right now. Please contact support.';
        }

        if (str_contains($normalized, 'timeout') || str_contains($normalized, 'connection')) {
            return 'AI service connection issue. Please retry shortly.';
        }

        return 'Failed to generate AI response. Please try again.';
    }

    private function buildSystemPrompt(): string
    {
        $basePrompt = $this->readFileContent(config('ai.system_prompt_file'));

        $projectContext = $this->readFileContent(config('ai.project_context_file'));

        $languageCode = $this->detectLanguage();

        $languagePrompt = '';

        if ($languageCode !== null) {
            $languagePrompts = config('ai.language_prompts', []);

            if (is_array($languagePrompts) && isset($languagePrompts[$languageCode])) {
                $languagePrompt = ' '.$languagePrompts[$languageCode];
            }
        }

        $relevantTopics = config('ai.relevant_topics', []);
        $relevantTopicsText = is_array($relevantTopics) && $relevantTopics !== []
            ? 'You can help with: '.implode(', ', $relevantTopics).'.'
            : '';

        $offTopicResponse = (string) config('ai.off_topic_response', '');

        $relevantPrompt = '';

        if ($relevantTopicsText !== '') {
            $relevantPrompt .= ' '.$relevantTopicsText;
        }

        if ($offTopicResponse !== '') {
            $relevantPrompt .= ' If asked about unrelated topics, respond: '.$offTopicResponse;
        }

        $parts = array_filter([
            $basePrompt,
            $projectContext,
            $languagePrompt,
            $relevantPrompt,
        ]);

        return implode("\n\n", $parts);
    }

    private function readFileContent(string $path): string
    {
        if ($path === '' || ! file_exists($path)) {
            return '';
        }

        $content = file_get_contents($path);

        return $content !== false ? trim($content) : '';
    }

    private function detectLanguage(): ?string
    {
        if ($this->language !== null && $this->language !== '') {
            $code = mb_strtolower(mb_substr($this->language, 0, 2));

            $supported = ['km', 'en'];

            if (in_array($code, $supported, true)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function chunkText(string $text, int $size = 120): array
    {
        if ($text === '') {
            return [''];
        }

        $chunks = [];
        $length = mb_strlen($text);

        for ($offset = 0; $offset < $length; $offset += $size) {
            $chunks[] = mb_substr($text, $offset, $size);
        }

        return $chunks;
    }
}
