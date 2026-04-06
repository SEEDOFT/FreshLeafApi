<?php

namespace App\Services;

use App\Services\Contracts\AiProviderContract;
use Exception;
use Throwable;

class AiService
{
    public function __construct(
        private GeminiService $geminiService,
        private OllamaService $ollamaService,
        private ZenService $zenService,
    ) {}

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
     * @return array<string, AiProviderContract>
     */
    private function resolveProviders(): array
    {
        $requested = (string) config('ai.default', 'gemini');
        $fallbacks = config('ai.fallbacks', []);

        if (! is_array($fallbacks)) {
            $fallbacks = [];
        }

        $orderedProviders = array_values(array_unique(array_filter([
            $requested,
            ...$fallbacks,
        ])));

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
        }

        if ($providers === []) {
            $providers['gemini'] = $this->geminiService;
        }

        return $providers;
    }
}
