<?php

namespace App\Services\Contracts;

interface AiProviderContract
{
    public function generateContent(string $prompt, array $options = []): string;

    public function generateContentWithHistory(array $history, string $prompt, array $options = []): string;

    public function generateContentWithSystemPrompt(
        string $systemPrompt,
        string $prompt,
        array $options = [],
    ): string;

    public function generateContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        array $options = [],
    ): string;
}
