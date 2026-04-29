<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Services\Contracts\AiProviderContract;
use Exception;
use Override;

class AiService implements AiProviderContract
{
    /**
     * AiService constructor.
     *
     * @param  GeminiService  $geminiService  The Gemini AI provider service.
     * @param  OllamaService  $ollamaService  The Ollama AI provider service.
     */
    public function __construct(
        private GeminiService $geminiService,
        private OllamaService $ollamaService,
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateContent(string $prompt, array $options = []): string
    {
        return $this->resolveProvider()->generateContent($prompt, $options);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateContentWithHistory(array $history, string $prompt, array $options = []): string
    {
        return $this->resolveProvider()->generateContentWithHistory($history, $prompt, $options);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateContentWithSystemPrompt(
        string $systemPrompt,
        string $prompt,
        array $options = [],
    ): string {
        return $this->resolveProvider()->generateContentWithSystemPrompt($systemPrompt, $prompt, $options);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        array $options = [],
    ): string {
        return $this->resolveProvider()->generateContentWithSystemPromptAndHistory($systemPrompt, $history, $prompt, $options);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function streamContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        callable $onChunk,
        array $options = [],
    ): string {
        return $this->resolveProvider()->streamContentWithSystemPromptAndHistory($systemPrompt, $history, $prompt, $onChunk, $options);
    }

    /**
     * Resolve the single configured AI provider.
     */
    private function resolveProvider(): AiProviderContract
    {
        $providerName = (string) \config('ai.default', 'ollama');

        return match ($providerName) {
            'gemini' => $this->geminiService,
            'ollama' => $this->ollamaService,
            default => throw new Exception("Configured AI provider [{$providerName}] is not supported."),
        };
    }
}
