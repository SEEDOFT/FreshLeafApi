<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface AiProviderContract
{
    /**
     * Generate content based on the given prompt and options.
     *
     * @param  string  $prompt  The input prompt for content generation.
     * @param  array  $options  Additional options for content generation (e.g., temperature, max tokens).
     * @return string The generated content.
     */
    public function generateContent(string $prompt, array $options = []): string;

    /**
     * Generate content based on the given history, prompt, and options.
     *
     * @param  array  $history  An array of previous interactions or messages.
     * @param  string  $prompt  The input prompt for content generation.
     * @param  array  $options  Additional options for content generation (e.g., temperature, max tokens).
     * @return string The generated content.
     */
    public function generateContentWithHistory(array $history, string $prompt, array $options = []): string;

    /**
     * Generate content based on the given system prompt, prompt, and options.
     *
     * @param  string  $systemPrompt  The system prompt for content generation.
     * @param  string  $prompt  The input prompt for content generation.
     * @param  array  $options  Additional options for content generation (e.g., temperature, max tokens).
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
     * @param  array  $history  An array of previous interactions or messages.
     * @param  string  $prompt  The input prompt for content generation.
     * @param  array  $options  Additional options for content generation (e.g., temperature, max tokens).
     * @return string The generated content.
     */
    public function generateContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        array $options = [],
    ): string;
}
