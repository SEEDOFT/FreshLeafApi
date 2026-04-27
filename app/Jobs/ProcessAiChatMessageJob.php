<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\AiMessageChunk;
use App\Events\AiMessageCompleted;
use App\Events\AiMessageFailed;
use App\Events\AiMessageStarted;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Services\Ai\AiService;
use App\Services\Ai\WebSearchService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiChatMessageJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
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

    /**
     * Handle the job.
     */
    public function handle(AiService $aiService, WebSearchService $searchService): void
    {
        $session = AiChatSession::where('session_id', $this->sessionId)
            ->where('user_id', $this->userId)
            ->first();

        $assistantMessage = AiChatMessage::where('message_id', $this->messageId)
            ->where('ai_chat_session_id', $session?->id)
            ->first();

        if (! $session || ! $assistantMessage) {
            Log::warning('AI Bridge: Session or message not found', [
                'session_id' => $this->sessionId,
                'message_id' => $this->messageId,
            ]);

            $this->broadcastFailure('Session not found');

            return;
        }

        $sequence = \max(1, (int) $assistantMessage->sequence);
        \event(new AiMessageStarted(
            userId: $this->userId,
            sessionId: $this->sessionId,
            messageId: $this->messageId,
            role: 'assistant',
            sequence: $sequence,
            timestamp: \now()->toIso8601String(),
        ));

        try {
            $history = AiChatMessage::where('ai_chat_session_id', $session->id)
                ->where('id', '<', $assistantMessage->id)
                ->whereIn('role', ['user', 'assistant'])
                ->orderBy('id')
                ->get(['role', 'content'])
                ->map(static fn ($m) => ['role' => $m->role, 'content' => $m->content])
                ->all();

            $options = ['temperature' => $this->temperature ?? 0.7, 'maxOutputTokens' => $this->maxOutputTokens ?? 4096];
            $systemPrompt = $this->buildSystemPrompt();

            $finalText = '';

            if ($this->shouldSearchBeforeModel($this->prompt)) {
                $finalText = $this->answerWithSearch(
                    aiService: $aiService,
                    searchService: $searchService,
                    systemPrompt: $systemPrompt,
                    history: $history,
                    query: $this->prompt,
                    options: $options,
                    sequence: $sequence,
                );
            } else {
                // 1. Ask local AI if it needs a search
                $initialResponse = $aiService->generateContentWithSystemPromptAndHistory(
                    systemPrompt: $systemPrompt,
                    history: $history,
                    prompt: $this->prompt,
                    options: $options,
                );

                $finalText = $initialResponse;
                $query = $this->extractSearchQuery($initialResponse);

                // 2. Handle Hybrid Search if requested
                if ($query !== '') {
                    if ($this->shouldHonorModelSearchRequest($query)) {
                        $finalText = $this->answerWithSearch(
                            aiService: $aiService,
                            searchService: $searchService,
                            systemPrompt: $systemPrompt,
                            history: $history,
                            query: $query,
                            options: $options,
                            sequence: $sequence,
                        );
                    } else {
                        $normalPrompt = $this->buildNormalAnswerPrompt($query);
                        $finalText = $aiService->streamContentWithSystemPromptAndHistory(
                            systemPrompt: $systemPrompt,
                            history: $history,
                            prompt: $normalPrompt,
                            onChunk: function (string $chunk) use (&$sequence): void {
                                $this->broadcastAssistantChunk($chunk, $sequence);
                            },
                            options: $options,
                        );
                    }
                } else {
                    // Regular response (no search needed)
                    // BROADCAST the initial response so user actually sees it!
                    $this->broadcastAssistantChunk($initialResponse, $sequence, trim: true);
                }
            }

            $finalText = $this->sanitizeAssistantOutput($finalText);

            $assistantMessage->update([
                'content' => $finalText,
                'status' => 'done',
                'sequence' => $sequence + 1,
            ]);

            \event(new AiMessageCompleted(
                userId: $this->userId,
                sessionId: $this->sessionId,
                messageId: $this->messageId,
                role: 'assistant',
                fullText: $finalText,
                sequence: $sequence + 1,
                timestamp: \now()->toIso8601String(),
            ));

        } catch (Exception $e) {
            Log::error('AI Bridge: Chat message processing failed', [
                'session_id' => $this->sessionId,
                'message_id' => $this->messageId,
                'error' => $e->getMessage(),
            ]);

            $clientError = $this->toClientSafeError($e->getMessage());

            $assistantMessage->update([
                'status' => 'failed',
                'error' => $clientError,
                'sequence' => $sequence + 1,
            ]);

            $this->broadcastFailure($clientError);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $history
     * @param  array<string, mixed>  $options
     */
    private function answerWithSearch(
        AiService $aiService,
        WebSearchService $searchService,
        string $systemPrompt,
        array $history,
        string $query,
        array $options,
        int &$sequence,
    ): string {
        \event(new AiMessageChunk(
            userId: $this->userId,
            sessionId: $this->sessionId,
            messageId: $this->messageId,
            role: 'assistant',
            textChunk: "\n*(Accessing internet for: {$query}...)*\n\n",
            sequence: ++$sequence,
            timestamp: \now()->toIso8601String(),
        ));

        $searchResult = $searchService->search($query);
        $finalPrompt = "Internet Facts:\n\n{$searchResult}\n\nUser Question: '{$this->prompt}'. Use the facts to give a complete answer.";

        return $aiService->streamContentWithSystemPromptAndHistory(
            systemPrompt: $systemPrompt,
            history: $history,
            prompt: $finalPrompt,
            onChunk: function (string $chunk) use (&$sequence): void {
                $this->broadcastAssistantChunk($chunk, $sequence);
            },
            options: $options,
        );
    }

    private function extractSearchQuery(string $text): string
    {
        if (! \str_contains($text, '[SEARCH_REQUIRED:')) {
            return '';
        }

        \preg_match('/\[SEARCH_REQUIRED:\s*(.*?)\]/', $text, $matches);

        return \trim((string) ($matches[1] ?? ''));
    }

    private function shouldSearchBeforeModel(string $prompt): bool
    {
        if (! (bool) \config('ai.web_search.enabled', true)) {
            return false;
        }

        $normalizedPrompt = \mb_strtolower($prompt);
        /** @var array<int, string> $keywords */
        $keywords = \config('ai.web_search.live_query_keywords', []);

        foreach ($keywords as $keyword) {
            $normalizedKeyword = \mb_strtolower(\trim($keyword));

            if ($normalizedKeyword !== '' && \str_contains($normalizedPrompt, $normalizedKeyword)) {
                return true;
            }
        }

        return false;
    }

    private function shouldHonorModelSearchRequest(string $query): bool
    {
        if (! (bool) \config('ai.web_search.enabled', true)) {
            Log::info('AI Bridge: Ignoring model search request because web search is disabled', [
                'session_id' => $this->sessionId,
                'message_id' => $this->messageId,
                'prompt' => $this->prompt,
                'query' => $query,
            ]);

            return false;
        }

        return true;
    }

    private function buildNormalAnswerPrompt(string $ignoredSearchQuery): string
    {
        return "The previous draft incorrectly requested internet search with: [SEARCH_REQUIRED: {$ignoredSearchQuery}]. "
            .'Do not output any SEARCH_REQUIRED tag, no-search tag, or explanation of search routing. '
            .'Use the FreshLeaf project context when relevant, then answer the original user message naturally with helpful detail: "'
            .$this->prompt.'"';
    }

    private function sanitizeAssistantOutput(string $text, bool $trim = true): string
    {
        $text = (string) \preg_replace('/\s*\[SEARCH_REQUIRED:\s*.*?\]\s*/is', ' ', $text);
        $text = (string) \preg_replace(
            '/\s*\[(?=[^\]]*(?:no\s+search|search\s+tag\s+(?:not\s+)?required|query\s+can\s+be\s+(?:answered|addressed)\s+directly))[^\]]*\]\.?\s*/iu',
            ' ',
            $text,
        );
        $text = (string) \preg_replace('/[ \t]+([.,!?;:])/', '$1', $text);
        $text = (string) \preg_replace('/[ \t]+\r?\n/', "\n", $text);
        $text = (string) \preg_replace('/\n{3,}/', "\n\n", $text);
        $text = (string) \preg_replace('/ {2,}/', ' ', $text);

        return $trim ? \trim($text) : $text;
    }

    private function broadcastAssistantChunk(string $chunk, int &$sequence, bool $trim = false): void
    {
        $chunk = $this->sanitizeAssistantOutput($chunk, $trim);

        if (\trim($chunk) === '') {
            return;
        }

        \event(new AiMessageChunk(
            userId: $this->userId,
            sessionId: $this->sessionId,
            messageId: $this->messageId,
            role: 'assistant',
            textChunk: $chunk,
            sequence: ++$sequence,
            timestamp: \now()->toIso8601String(),
        ));
    }

    private function toClientSafeError(string $message): string
    {
        $normalized = \mb_strtolower($message);

        if (\str_contains($normalized, 'llama.cpp connection failed') || \str_contains($normalized, '127.0.0.1:9000')) {
            return 'Local llama.cpp is not reachable at 127.0.0.1:9000. Start the local AI server and retry.';
        }

        if (\str_contains($normalized, 'ollama connection failed') || \str_contains($normalized, '127.0.0.1:11434')) {
            return 'Ollama is not reachable at 127.0.0.1:11434. Remove it from AI_PROVIDER or AI_FALLBACK_PROVIDERS, or start Ollama.';
        }

        if (\str_contains($normalized, 'no configured ai provider')) {
            return 'No configured AI provider is available. Check AI_PROVIDER and AI_FALLBACK_PROVIDERS.';
        }

        if (\str_contains($normalized, 'timeout') || \str_contains($normalized, 'connection')) {
            return 'AI service connection issue. Please retry shortly.';
        }

        return 'Failed to generate AI response. Please try again.';
    }

    private function broadcastFailure(string $error): void
    {
        \event(new AiMessageFailed(
            userId: $this->userId,
            sessionId: $this->sessionId,
            messageId: $this->messageId,
            role: 'assistant',
            error: $error,
            sequence: 100,
            timestamp: \now()->toIso8601String(),
        ));
    }

    private function buildSystemPrompt(): string
    {
        $base = $this->readFileContent(\config('ai.system_prompt_file'));
        $ctx = $this->readFileContent(\config('ai.project_context_file'));
        $languagePrompt = $this->resolveLanguagePrompt();

        return "{$base}\n\n{$languagePrompt}\n\n{$ctx}";
    }

    private function resolveLanguagePrompt(): string
    {
        if ($this->containsKhmerText($this->prompt)) {
            return 'Current conversation language guidance: The user wrote Khmer. Reply in natural, fluent Khmer unless the user asks for another language.';
        }

        $language = \mb_strtolower((string) $this->language);

        if (\str_contains($language, 'km') || \str_contains($language, 'kh')) {
            return 'Current conversation language guidance: The request language is Khmer. Reply in natural, fluent Khmer unless the user asks for another language.';
        }

        if (\str_contains($language, 'en')) {
            return 'Current conversation language guidance: The request language is English. Reply in fluent English unless the user asks for another language.';
        }

        return 'Current conversation language guidance: Match the user message language. Use Khmer for Khmer, English for English, and natural mixed Khmer-English when the user mixes both.';
    }

    private function containsKhmerText(string $text): bool
    {
        return \preg_match('/[\x{1780}-\x{17FF}]/u', $text) === 1;
    }

    private function readFileContent(string $path): string
    {
        return (\file_exists($path)) ? \trim(\file_get_contents($path)) : '';
    }
}
