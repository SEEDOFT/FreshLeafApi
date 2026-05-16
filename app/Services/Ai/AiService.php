<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Exceptions\AiProviderUnavailableException;
use App\Services\Contracts\AiProviderContract;
use Exception;
use Override;
use Throwable;

use function config;
use function in_array;
use function str_contains;
use function strtolower;
use function trim;

class AiService implements AiProviderContract
{
    /**
     * AiService constructor.
     *
     * @param  GeminiService  $geminiService  The Gemini AI provider service.
     * @param  OllamaService  $ollamaService  The Ollama AI provider service.
     * @param  ZenService  $zenService  The Zen AI provider service.
     */
    public function __construct(
        private GeminiService $geminiService,
        private OllamaService $ollamaService,
        private ZenService $zenService,
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function healthCheck(): bool
    {
        try {
            $this->assertAvailable();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Ensure the configured provider is usable before queueing work.
     *
     * @throws AiProviderUnavailableException
     */
    public function assertAvailable(): void
    {
        $providerName = $this->providerName();
        $provider = $this->resolveProvider();

        if ($providerName === 'gemini' && trim((string) config('ai.providers.gemini.api_key')) === '') {
            throw new AiProviderUnavailableException('Gemini is unavailable.');
        }

        if ($providerName === 'ollama') {
            if (trim((string) config('ai.providers.ollama.base_url')) === ''
                || trim((string) config('ai.providers.ollama.model')) === '') {
                throw new AiProviderUnavailableException(
                    'Ollama is unavailable. Please start Ollama and confirm the configured model exists.'
                );
            }
        }

        if (! $provider->healthCheck()) {
            throw new AiProviderUnavailableException(match ($providerName) {
                'gemini' => 'Gemini is unavailable.',
                'ollama' => 'Ollama is unavailable. Please start Ollama and confirm the configured model exists.',
                'zen' => 'Zen is unavailable.',
                default => "Configured AI provider [{$providerName}] is not supported.",
            });
        }
    }

    public function normalizeFailureMessage(Throwable|string $error): string
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;
        $message = trim($message);
        $lower = strtolower($message);

        if ($message === '') {
            return 'AI provider returned an empty response.';
        }

        if (str_contains($lower, '429')
            || str_contains($lower, 'quota')
            || str_contains($lower, 'rate limit')) {
            return 'AI usage limit reached. Please try again later or switch provider.';
        }

        if (str_contains($lower, 'gemini')) {
            if (str_contains($lower, 'empty response') || str_contains($lower, 'no text content')) {
                return 'AI provider returned an empty response.';
            }

            return 'Gemini is unavailable.';
        }

        if (str_contains($lower, 'ollama')) {
            if (str_contains($lower, 'empty response')) {
                return 'AI provider returned an empty response.';
            }

            return 'Ollama is unavailable. Please start Ollama and confirm the configured model exists.';
        }

        if (str_contains($lower, 'empty response') || str_contains($lower, 'no text content')) {
            return 'AI provider returned an empty response.';
        }

        return $message;
    }

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
    public function generateContentWithHistory(
        array $history,
        string $prompt,
        array $options = []
    ): string {
        return $this->resolveProvider()
            ->generateContentWithHistory($history, $prompt, $options);
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
        return $this->resolveProvider()
            ->generateContentWithSystemPrompt($systemPrompt, $prompt, $options);
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
        return $this->resolveProvider()
            ->generateContentWithSystemPromptAndHistory(
                $systemPrompt,
                $history,
                $prompt,
                $options,
            );
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
        return $this->resolveProvider()
            ->streamContentWithSystemPromptAndHistory(
                $systemPrompt,
                $history,
                $prompt,
                $onChunk,
                $options,
            );
    }

    /**
     * Resolve the single configured AI provider.
     */
    private function resolveProvider(): AiProviderContract
    {
        $providerName = $this->providerName();

        return match ($providerName) {
            'gemini' => $this->geminiService,
            'ollama' => $this->ollamaService,
            'zen' => $this->zenService,
            default => throw new Exception(
                "Configured AI provider [{$providerName}] is not supported."
            ),
        };
    }

    private function providerName(): string
    {
        $providerName = strtolower(trim((string) config('ai.default', 'ollama')));

        if (! in_array($providerName, ['gemini', 'ollama', 'zen'], true)) {
            throw new AiProviderUnavailableException(
                "Configured AI provider [{$providerName}] is not supported."
            );
        }

        return $providerName;
    }
}
