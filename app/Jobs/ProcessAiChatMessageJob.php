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
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcessAiChatMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public private(set) int $userId;

    public private(set) string $sessionId;

    public private(set) string $messageId;

    public private(set) string $prompt;

    public private(set) ?string $language;

    public private(set) ?float $temperature;

    public private(set) ?int $maxOutputTokens;

    public private(set) array $history;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int|AiChatMessage $userId,
        ?string $sessionId = null,
        ?string $messageId = null,
        ?string $prompt = null,
        ?string $language = 'en',
        ?float $temperature = null,
        ?int $maxOutputTokens = null,
        array $history = []
    ) {
        $this->queue = 'ai-stream';
        if ($userId instanceof AiChatMessage) {
            $this->userId = (int) $userId->user_id;
            $this->sessionId = (string) $userId->session->session_id;
            $this->messageId = (string) $userId->message_id;
            $this->prompt = $prompt ?? '';
            $this->language = $language;
            $this->temperature = $temperature;
            $this->maxOutputTokens = $maxOutputTokens;
            $this->history = $history;
        } else {
            $this->userId = $userId;
            $this->sessionId = $sessionId ?? '';
            $this->messageId = $messageId ?? '';
            $this->prompt = $prompt ?? '';
            $this->language = $language;
            $this->temperature = $temperature;
            $this->maxOutputTokens = $maxOutputTokens;
            $this->history = $history;
        }
    }

    /**
     * Execute the job.
     */
    public function handle(AiService $aiService, WebSearchService $webSearchService): void
    {
        $assistantMessage = AiChatMessage::where('message_id', $this->messageId)->first();
        $session = AiChatSession::where('session_id', $this->sessionId)->first();

        if (! $assistantMessage || ! $session) {
            Log::error('AI Job failed: Message or Session not found', [
                'message_id' => $this->messageId,
                'session_id' => $this->sessionId,
            ]);

            return;
        }

        $userId = $this->userId;
        $sessionId = $this->sessionId;

        // 1. Initial Start Event
        $sequence = max(1, (int) $assistantMessage->sequence);
        event(new AiMessageStarted(
            userId: $userId,
            sessionId: $sessionId,
            messageId: (string) $assistantMessage->message_id,
            role: 'assistant',
            sequence: $sequence,
            timestamp: now()->toIso8601String(),
        ));

        $fullResponse = '';
        try {
            // 2. Determine if initial search is needed
            $searchQuery = $this->extractSearchQuery($this->prompt);
            $context = '';

            if ($searchQuery !== '' && (bool) config('ai.web_search.enabled', true)) {
                $this->broadcastChunk($assistantMessage, $userId, $sessionId, " [Accessing internet to search for: {$searchQuery}]... \n\n", ++$sequence);
                $context = $webSearchService->search($searchQuery);
            } elseif ($this->shouldPerformLiveSearch($this->prompt)) {
                $this->broadcastChunk($assistantMessage, $userId, $sessionId, " [Performing live search]... \n\n", ++$sequence);
                $context = $webSearchService->search($this->prompt);
            }

            $finalPrompt = $this->prompt;
            if ($context !== '') {
                $finalPrompt = "Context from web search:\n\n{$context}\n\nUser Question: {$this->prompt}";
            }

            // 3. Get Response
            if ($context !== '') {
                // We have context, so we stream the response
                $fullResponse = $this->streamAiResponse($aiService, $assistantMessage, $finalPrompt, $sequence);
            } else {
                // No initial search, try generating first
                $fullResponse = $aiService->generateContentWithSystemPromptAndHistory(
                    systemPrompt: $this->getSystemPrompt($assistantMessage),
                    history: $this->history,
                    prompt: $finalPrompt
                );

                // Check if the response contains a search tag
                if (preg_match('/\[SEARCH_REQUIRED:\s*(.*?)\]/i', $fullResponse, $matches)) {
                    $secondarySearchQuery = trim($matches[1]);
                    $this->broadcastChunk($assistantMessage, $userId, $sessionId, " [Accessing internet to search for: {$secondarySearchQuery}]... \n\n", ++$sequence);

                    $secondaryContext = $webSearchService->search($secondarySearchQuery);
                    $secondaryPrompt = "Context from web search:\n\n{$secondaryContext}\n\nUser Question: {$this->prompt}";

                    // Now stream with the new context
                    $fullResponse = $this->streamAiResponse($aiService, $assistantMessage, $secondaryPrompt, $sequence);
                } else {
                    // Just a regular response, broadcast it
                    $this->broadcastChunk($assistantMessage, $userId, $sessionId, $this->cleanResponse($fullResponse), ++$sequence);
                }
            }

            // Check if we stopped early
            if (Cache::has("ai_stop_{$assistantMessage->message_id}")) {
                throw new \Exception('STOP_SIGNAL');
            }

            // 4. Finalize Message
            $cleanResponse = $this->cleanResponse($fullResponse);
            $assistantMessage->update([
                'content' => $cleanResponse,
                'status' => 'done',
            ]);

            // 5. Completion Event
            event(new AiMessageCompleted(
                userId: $userId,
                sessionId: $sessionId,
                messageId: (string) $assistantMessage->message_id,
                role: 'assistant',
                fullText: $cleanResponse,
                sequence: $sequence,
                timestamp: now()->toIso8601String(),
            ));

        } catch (\Exception $e) {
            // Re-throw PHPUnit exceptions so tests don't fail silently
            if (str_starts_with(get_class($e), 'PHPUnit\\')) {
                throw $e;
            }

            // Handle user-initiated stop separately
            if ($e->getMessage() === 'STOP_SIGNAL') {
                Cache::forget("ai_stop_{$assistantMessage->message_id}");
                Log::info('AI Job stopped by user', ['message_id' => $assistantMessage->id]);

                return;
            }

            Log::error('AI Processing failed', [
                'message_id' => $assistantMessage->id,
                'error' => $e->getMessage(),
            ]);

            $error = $this->formatErrorMessage($e->getMessage());

            $assistantMessage->update([
                'status' => 'failed',
                'error' => $error,
            ]);

            event(new AiMessageFailed(
                userId: $userId,
                sessionId: $sessionId,
                messageId: (string) $assistantMessage->message_id,
                role: 'assistant',
                error: $error,
                sequence: $sequence,
                timestamp: now()->toIso8601String(),
            ));
        }
    }

    /**
     * Stream the AI response and return the full text.
     */
    private function streamAiResponse(AiService $aiService, AiChatMessage $assistantMessage, string $prompt, int &$sequence): string
    {
        $buffer = '';
        $fullResponse = $aiService->streamContentWithSystemPromptAndHistory(
            systemPrompt: $this->getSystemPrompt($assistantMessage),
            history: $this->history,
            prompt: $prompt,
            onChunk: function (string $chunk) use (&$sequence, &$buffer, $assistantMessage): void {
                if (Cache::has("ai_stop_{$assistantMessage->message_id}")) {
                    throw new \Exception('STOP_SIGNAL');
                }

                $buffer .= $chunk;

                if (str_contains($chunk, "\n") || count(preg_split('/(?<=[.!?])\s+/', $buffer)) > 1) {
                    $this->broadcastChunk($assistantMessage, $this->userId, $this->sessionId, $buffer, ++$sequence);
                    $buffer = '';
                }
            }
        );

        if ($buffer !== '') {
            $this->broadcastChunk($assistantMessage, $this->userId, $this->sessionId, $buffer, ++$sequence);
        }

        return $fullResponse;
    }

    /**
     * Get the system prompt based on config and user locale.
     */
    private function getSystemPrompt(AiChatMessage $assistantMessage): string
    {
        $systemPromptPath = (string) config('ai.system_prompt_file');
        $projectContextPath = (string) config('ai.project_context_file');

        $prompt = '';
        if ($systemPromptPath !== '' && File::exists($systemPromptPath)) {
            $prompt = File::get($systemPromptPath);
        }

        if ($projectContextPath !== '' && File::exists($projectContextPath)) {
            $context = File::get($projectContextPath);
            $prompt .= "\n\n".$context;
        }

        $session = $assistantMessage->session;
        $user = $session->user;

        if (! $user) {
            return $prompt;
        }

        $locale = $this->language ?: ($user->profile?->locale ?? config('app.locale', 'en'));
        $languagePrompt = (string) config("ai.language_prompts.{$locale}", "Please respond in the following language: {$locale}");

        return $prompt."\n\n".$languagePrompt;
    }

    /**
     * Broadcast a chunk to the user via WebSocket.
     */
    private function broadcastChunk(AiChatMessage $message, int $userId, string $sessionId, string $content, int $sequence): void
    {
        if ($content === '') {
            return;
        }

        broadcast(new AiMessageChunk(
            userId: $userId,
            sessionId: $sessionId,
            messageId: (string) $message->message_id,
            role: 'assistant',
            textChunk: $content,
            sequence: $sequence,
            timestamp: now()->toIso8601String(),
        ));
    }

    /**
     * Clean the response content.
     */
    private function cleanResponse(string $content): string
    {
        $content = trim($content);

        // Remove internal notes often generated by model when search is discussed
        $content = (string) preg_replace('/\[No search tag required[^\]]*\]\.?/i', '', $content);

        return trim($content);
    }

    /**
     * Format the error message for the user.
     */
    private function formatErrorMessage(string $message): string
    {
        if (str_contains($message, 'rate limit')) {
            return 'AI rate limit reached. Please try again in a few minutes.';
        }

        if (str_contains($message, 'No configured AI provider')) {
            return 'No configured AI provider is available. Check AI_PROVIDER and AI_FALLBACK_PROVIDERS.';
        }

        return $message;
    }

    /**
     * Extract a search query from the prompt if needed.
     */
    private function extractSearchQuery(string $prompt): string
    {
        // Simple heuristic: if prompt is long or asks for recent info
        if (preg_match('/(current|latest|today|search|news|weather|price of)/i', $prompt)) {
            return $prompt;
        }

        return '';
    }

    /**
     * Determine if a live search should be performed.
     */
    private function shouldPerformLiveSearch(string $prompt): bool
    {
        return false;
    }
}
