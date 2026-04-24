<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Services\Contracts\AiProviderContract;
use Exception;
use Override;
use Throwable;

class AiService implements AiProviderContract
{
    /**
     * AiService constructor.
     *
     * @param  GeminiService  $geminiService  The Gemini AI provider service.
     * @param  OllamaService  $ollamaService  The Ollama AI provider service.
     * @param  ZenService  $zenService  The Zen AI provider service.
     * @param  LlamaCppService  $llamaCppService  The Llama.cpp AI provider service.
     */
    public function __construct(
        private GeminiService $geminiService,
        private OllamaService $ollamaService,
        private ZenService $zenService,
        private LlamaCppService $llamaCppService,
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateContent(string $prompt, array $options = []): string
    {
        $providers = $this->resolveProviders();
        $lastException = null;

        foreach ($providers as $providerName => $provider) {
            try {
                return $provider->generateContent($prompt, $options);
            } catch (Throwable $exception) {
                $lastException = new Exception($providerName.': '.$exception->getMessage(), previous: $exception);
            }
        }

        throw $lastException ?? new Exception('No AI provider available');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateContentWithHistory(array $history, string $prompt, array $options = []): string
    {
        $providers = $this->resolveProviders();
        $lastException = null;

        foreach ($providers as $providerName => $provider) {
            try {
                return $provider->generateContentWithHistory($history, $prompt, $options);
            } catch (Throwable $exception) {
                $lastException = new Exception($providerName.': '.$exception->getMessage(), previous: $exception);
            }
        }

        throw $lastException ?? new Exception('No AI provider available');
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
        $providers = $this->resolveProviders();
        $lastException = null;

        foreach ($providers as $providerName => $provider) {
            try {
                return $provider->generateContentWithSystemPrompt($systemPrompt, $prompt, $options);
            } catch (Throwable $exception) {
                $lastException = new Exception($providerName.': '.$exception->getMessage(), previous: $exception);
            }
        }

        throw $lastException ?? new Exception('No AI provider available');
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
        $providers = $this->resolveProviders();
        $lastException = null;

        foreach ($providers as $providerName => $provider) {
            try {
                return $provider->generateContentWithSystemPromptAndHistory($systemPrompt, $history, $prompt, $options);
            } catch (Throwable $exception) {
                $lastException = new Exception($providerName.': '.$exception->getMessage(), previous: $exception);
            }
        }

        throw $lastException ?? new Exception('No AI provider available');
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
        $providers = $this->resolveProviders();
        $lastException = null;

        foreach ($providers as $providerName => $provider) {
            try {
                return $provider->streamContentWithSystemPromptAndHistory($systemPrompt, $history, $prompt, $onChunk, $options);
            } catch (Throwable $exception) {
                $lastException = new Exception($providerName.': '.$exception->getMessage(), previous: $exception);
            }
        }

        throw $lastException ?? new Exception('No AI provider available');
    }

    /**
     * Resolve the AI providers based on the configuration and return them in the order of preference.
     * The configuration should specify the default provider and any fallback providers. The method will return an array of provider instances keyed by their names.
     * The method checks the configuration for the default provider and fallbacks, and constructs an ordered list of providers to use. If no valid providers are found in the configuration, it defaults to using the Gemini provider.
     *
     * @return array<string, AiProviderContract>
     */
    private function resolveProviders(): array
    {
        /** @var string $requested */
        $requested = \config('ai.default', 'gemini');
        /** @var array<int, string> $fallbacks */
        $fallbacks = \config('ai.fallbacks', []);

        $orderedProviders = [$requested, ...$fallbacks]
            |> \array_filter(...)
            |> \array_unique(...)
            |> \array_values(...);

        $providers = [];

        foreach ($orderedProviders as $providerName) {
            if ($providerName === 'gemini') {
                $providers['gemini'] = $this->geminiService;
            }

            if ($providerName === 'ollama') {
                $providers['ollama'] = $this->ollamaService;
            }

            if ($providerName === 'zen') {
                $providers['zen'] = $this->zenService;
            }

            if ($providerName === 'llama_cpp') {
                $providers['llama_cpp'] = $this->llamaCppService;
            }
        }

        if ($providers === []) {
            $providers['gemini'] = $this->geminiService;
        }

        return $providers;
    }
}
