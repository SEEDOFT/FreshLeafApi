<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Services\Contracts\AiProviderContract;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ZenService implements AiProviderContract
{
    private string $apiKey;

    private string $baseUrl;

    private string $model;

    private int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) \config('ai.providers.zen.api_key', '');
        $this->baseUrl = \rtrim((string) \config('ai.providers.zen.base_url', ''), '/');
        $this->model = (string) \config('ai.providers.zen.model', 'zen-free');
        $this->timeout = (int) \config('ai.providers.zen.timeout', 40);
    }

    public function generateContent(string $prompt, array $options = []): string
    {
        $messages = [
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];

        return $this->requestChat($messages, $options);
    }

    public function generateContentWithHistory(array $history, string $prompt, array $options = []): string
    {
        $messages = [];

        foreach ($history as $message) {
            $content = (string) ($message['content'] ?? '');

            if ($content === '') {
                continue;
            }

            $messages[] = [
                'role' => ($message['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                'content' => $content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        return $this->requestChat($messages, $options);
    }

    public function generateContentWithSystemPrompt(
        string $systemPrompt,
        string $prompt,
        array $options = [],
    ): string {
        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];

        return $this->requestChat($messages, $options);
    }

    public function generateContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        array $options = [],
    ): string {
        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
        ];

        foreach ($history as $message) {
            $content = (string) ($message['content'] ?? '');

            if ($content === '') {
                continue;
            }

            $messages[] = [
                'role' => ($message['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                'content' => $content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        return $this->requestChat($messages, $options);
    }

    private function requestChat(array $messages, array $options): string
    {
        if ($this->apiKey === '') {
            throw new Exception('Zen API key is missing');
        }

        if ($this->baseUrl === '') {
            throw new Exception('Zen API base URL is missing');
        }

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['maxOutputTokens'] ?? 4096,
        ];

        try {
            $response = Http::acceptJson()
                ->withToken($this->apiKey)
                ->connectTimeout(10)
                ->timeout($this->timeout)
                ->retry([200, 500, 1000], throw: false)
                ->post($this->baseUrl.'/chat/completions', $payload);
        } catch (ConnectionException $exception) {
            throw new Exception('Zen connection failed: '.$exception->getMessage());
        }

        if ($response->failed()) {
            throw new Exception($this->extractErrorMessage($response));
        }

        $content = (string) $response->json('choices.0.message.content', '');

        if ($content === '') {
            throw new Exception('Zen returned an empty response');
        }

        return $content;
    }

    private function extractErrorMessage(Response $response): string
    {
        $message = (string) ($response->json('error.message') ?? $response->json('message') ?? 'Unknown Zen API error');

        return 'Zen API error ('.$response->status().'): '.$message;
    }
}
