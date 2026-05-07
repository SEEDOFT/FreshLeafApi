<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\AiMessageChunk;
use App\Events\AiMessageCompleted;
use App\Events\AiMessageFailed;
use App\Events\AiMessageStarted;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\VendorInventory;
use App\Services\Ai\AiService;
use App\Services\Ai\WebSearchService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use function sprintf;
use function stripos;

class ProcessAiChatMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const float DEFAULT_TEMPERATURE = 0.7;

    private const int DEFAULT_MAX_OUTPUT_TOKENS = 4096;

    public private(set) int $userId;

    public private(set) string $sessionId;

    public private(set) string $messageId;

    public private(set) string $prompt;

    public private(set) ?string $language;

    public private(set) ?float $temperature;

    public private(set) ?int $maxOutputTokens;

    /** @var array<int, array<string, string>> */
    public array $history;

    /**
     * Create a new job instance.
     *
     * @param  array<int, array<string, string>>  $history
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

    private function needsProductContext(string $prompt): bool
    {
        $keywords = ['price', 'stock', 'available', 'product', 'carrot', 'lettuce', 'tomato', 'morning glory'];
        foreach ($keywords as $keyword) {
            if (stripos($prompt, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function fetchProductContext(string $prompt): string
    {
        $inventoryItems = VendorInventory::query()
            ->active()
            ->join('products', 'vendor_inventories.product_id', '=', 'products.id')
            ->join('units', 'vendor_inventories.unit_id', '=', 'units.id')
            ->where(static fn (Builder $query) => $query
                ->where('products.name_en', 'LIKE', "%{$prompt}%")
                ->orWhere('products.name_km', 'LIKE', "%{$prompt}%"))
            ->select([
                'products.name_en',
                'products.name_km',
                'vendor_inventories.price',
                'vendor_inventories.stock_quantity',
                'units.name as unit_name',
                'vendor_inventories.farm_location',
            ])
            ->limit(5)
            ->get();

        if ($inventoryItems->isEmpty()) {
            return '';
        }

        return $inventoryItems->map(static fn ($item) => sprintf(
            'Product: %s (%s) - Price: $%s, Stock: %s %s, Location: %s',
            $item->getAttribute('name_en'),
            $item->getAttribute('name_km'),
            $item->getAttribute('price'),
            $item->getAttribute('stock_quantity'),
            $item->getAttribute('unit_name'),
            $item->getAttribute('farm_location')
        ))->implode("\n");
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

        Log::info('AI chat job started', [
            'message_id' => $assistantMessage->id,
            'provider' => config('ai.default'),
        ]);

        $sequence = max(1, (int) $assistantMessage->sequence);

        $context = '';
        if ($this->needsProductContext($this->prompt)) {
            $context = $this->fetchProductContext($this->prompt);
            if ($context !== '') {
                $this->broadcastChunk($assistantMessage, $userId, $sessionId, " [Looking up product details] \n\n", ++$sequence);
            }
        }

        // 1. Initial Start Event
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
                $this->broadcastChunk(
                    $assistantMessage,
                    $userId,
                    $sessionId,
                    " [Accessing internet to search for: {$searchQuery}] \n\n", ++$sequence
                );
                $context = $webSearchService->search($searchQuery);
            } elseif ($this->shouldPerformLiveSearch($this->prompt)) {
                $this->broadcastChunk(
                    $assistantMessage,
                    $userId,
                    $sessionId,
                    " [Performing live search] \n\n", ++$sequence
                );
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
                // ALWAYS stream for local models to prevent blocking timeouts
                $fullResponse = $this->streamAiResponse($aiService, $assistantMessage, $finalPrompt, $sequence);

                if (Cache::has("ai_stop_{$assistantMessage->message_id}")) {
                    throw new Exception('STOP_SIGNAL');
                }

                // Check if the streamed response contains a search tag
                if (preg_match('/\[SEARCH_REQUIRED:\s*(.*?)\]/i', $fullResponse, $matches)) {
                    $secondarySearchQuery = trim($matches[1]);
                    $assistantMessage->forceFill([
                        'content' => '',
                        'status' => 'processing',
                    ])->save();
                    $this->broadcastChunk(
                        $assistantMessage,
                        $userId,
                        $sessionId,
                        "\n\n[Accessing internet to search for: {$secondarySearchQuery}] \n\n", ++$sequence
                    );

                    $secondaryContext = $webSearchService->search($secondarySearchQuery);
                    $secondaryPrompt =
                    "Context from web search:\n\n{$secondaryContext}\n\nUser Question: {$this->prompt}";

                    // Replace the internal search marker with the final answer.
                    $fullResponse = $this->streamAiResponse(
                        $aiService,
                        $assistantMessage,
                        $secondaryPrompt,
                        $sequence,
                        $secondaryContext
                    );
                }
            }

            // Check if we stopped early
            if (Cache::has("ai_stop_{$assistantMessage->message_id}")) {
                throw new Exception('STOP_SIGNAL');
            }

            // 4. Finalize Message
            $cleanResponse = $this->cleanResponse($fullResponse);
            $assistantMessage->update([
                'content' => $cleanResponse,
                'status' => 'done',
            ]);

            Log::info('AI chat job completed', [
                'message_id' => $assistantMessage->id,
                'characters' => \strlen($cleanResponse),
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

        } catch (Exception $e) {
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
    private function streamAiResponse(AiService $aiService, AiChatMessage $assistantMessage, string $prompt, int &$sequence, string $context = ''): string
    {
        $buffer = '';
        $persistedResponse = '';
        $hasLoggedFirstChunk = false;
        $fullResponse = $aiService->streamContentWithSystemPromptAndHistory(
            systemPrompt: $this->getSystemPrompt($assistantMessage, $context),
            history: $this->history,
            prompt: $prompt,
            onChunk: function (string $chunk) use (&$sequence, &$buffer, &$persistedResponse, &$hasLoggedFirstChunk, $assistantMessage): void {
                if (Cache::has("ai_stop_{$assistantMessage->message_id}")) {
                    throw new Exception('STOP_SIGNAL');
                }

                $buffer .= $chunk;
                $persistedResponse .= $chunk;
                $assistantMessage->forceFill([
                    'content' => $persistedResponse,
                    'status' => 'processing',
                ])->save();

                if (! $hasLoggedFirstChunk) {
                    $hasLoggedFirstChunk = true;
                    Log::info('AI chat first chunk streamed', [
                        'message_id' => $assistantMessage->id,
                    ]);
                }

                if (str_contains($chunk, "\n") || count((array) preg_split('/(?<=[.!?])\s+/', $buffer)) > 1) {
                    $this->broadcastChunk($assistantMessage, $this->userId, $this->sessionId, $buffer, ++$sequence);
                    $buffer = '';
                }
            },
            options: $this->generationOptions(),
        );

        if ($buffer !== '') {
            $this->broadcastChunk($assistantMessage, $this->userId, $this->sessionId, $buffer, ++$sequence);
        }

        return $fullResponse;
    }

    /**
     * @return array{temperature: float, maxOutputTokens: int}
     */
    private function generationOptions(): array
    {
        return [
            'temperature' => $this->temperature ?? self::DEFAULT_TEMPERATURE,
            'maxOutputTokens' => $this->maxOutputTokens ?? self::DEFAULT_MAX_OUTPUT_TOKENS,
        ];
    }

    /**
     * Get the system prompt based on config and user locale.
     */
    private function getSystemPrompt(AiChatMessage $assistantMessage, string $context = ''): string
    {
        $systemPromptPath = (string) config('ai.system_prompt_file');
        $projectContextPath = (string) config('ai.project_context_file');

        $prompt = '';
        if ($systemPromptPath !== '' && File::exists($systemPromptPath)) {
            $prompt = File::get($systemPromptPath);
        }

        // Only inject context if requested or relevant
        if ($context !== '' && File::exists($projectContextPath)) {
            $prompt .= "\n\nContext from documentation:\n\n".$context;
        }

        $session = $assistantMessage->session;
        $user = $session->user;

        if (! $user) {
            return $prompt;
        }

        $locale = $this->language ?: $user->currentLocale;
        $languagePrompt = (string) config(
            "ai.language_prompts.{$locale}",
            "Please respond in the following language: {$locale}"
        );

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
