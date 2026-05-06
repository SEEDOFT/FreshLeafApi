<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface AiProviderContract
{
    /**
     * Check if the AI provider is available.
     *
     * @return bool True if available, false otherwise.
     */
    public function healthCheck(): bool;

    /**
     * Generate content based on the given prompt and options.
     *
     * @param  string  $prompt  The input prompt for content generation.
     * @param  array<string, mixed>  $options  Additional options for content generation (e.g., temperature, max tokens).
     * @return string The generated content.
     */
    public function generateContent(string $prompt, array $options = []): string;

    /**
     * Generate content based on the given history, prompt, and options.
     *
     * @param  array<int, array<string, mixed>>  $history  An array of previous interactions or messages.
     * @param  string  $prompt  The input prompt for content generation.
     * @param  array<string, mixed>  $options  Additional options for content generation (e.g., temperature, max tokens).
     * @return string The generated content.
     */
    public function generateContentWithHistory(array $history, string $prompt, array $options = []): string;

    /**
     * Generate content based on the given system prompt, prompt, and options.
     *
     * @param  string  $systemPrompt  The system prompt for content generation.
     * @param  string  $prompt  The input prompt for content generation.
     * @param  array<string, mixed>  $options  Additional options for content generation (e.g., temperature, max tokens).
     * @return string The generated content.
     */
    public function generateContentWithSystemPrompt(
        string $systemPrompt,
        string $prompt,
        array $options = [],
    ): string;

    /**
     * Generate content based on the given system prompt, history, prompt, and options.
     *
     * @param  string  $systemPrompt  The system prompt for content generation.
     * @param  array<int, array<string, mixed>>  $history  An array of previous interactions or messages.
     * @param  string  $prompt  The input prompt for content generation.
     * @param  array<string, mixed>  $options  Additional options for content generation (e.g., temperature, max tokens).
     * @return string The generated content.
     */
    public function generateContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        array $options = [],
    ): string;

    /**
     * Stream content based on the given system prompt, history, prompt, and options.
     *
     * @param  string  $systemPrompt  The system prompt for content generation.
     * @param  array<int, array<string, mixed>>  $history  An array of previous interactions or messages.
     * @param  string  $prompt  The input prompt for content generation.
     * @param  callable(string): void  $onChunk  A callback function that is invoked for each generated text chunk.
     * @param  array<string, mixed>  $options  Additional options for content generation.
     * @return string The full generated content.
     */
    public function streamContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        callable $onChunk,
        array $options = [],
    ): string;
}
