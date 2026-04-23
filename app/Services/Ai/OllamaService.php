<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Services\Contracts\AiProviderContract;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class OllamaService implements AiProviderContract
{
    private string $baseUrl;

    private string $model;

    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = \rtrim((string) \config('ai.providers.ollama.base_url', 'http://127.0.0.1:11434'), '/');
        $this->model = (string) \config('ai.providers.ollama.model', 'qwen2.5:1.5b');
        $this->timeout = (int) \config('ai.providers.ollama.timeout', 60);
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

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<string, mixed>  $options
     */
    private function requestChat(array $messages, array $options): string
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'num_predict' => $options['maxOutputTokens'] ?? 4096,
            ],
        ];

        try {
            $response = Http::acceptJson()
                ->connectTimeout(5)
                ->timeout($this->timeout)
                ->retry([200, 500], throw: false)
                ->post($this->baseUrl.'/api/chat', $payload);
        } catch (ConnectionException $exception) {
            throw new Exception('Ollama connection failed: '.$exception->getMessage());
        }

        if ($response->failed()) {
            throw new Exception($this->extractErrorMessage($response));
        }

        $text = (string) $response->json('message.content', '');

        if ($text === '') {
            throw new Exception('Ollama returned an empty response');
        }

        return $text;
    }

    private function extractErrorMessage(Response $response): string
    {
        $message = (string) $response->json('error', 'Unknown Ollama error');

        return 'Ollama error ('.$response->status().'): '.$message;
    }
}
