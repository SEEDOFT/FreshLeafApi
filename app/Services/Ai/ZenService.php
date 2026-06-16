<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Services\Contracts\AiProviderContract;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Override;
use Psr\Http\Message\StreamInterface;

use function config;

class ZenService implements AiProviderContract
{
    private string $apiKey;

    private string $baseUrl;

    private string $model;

    private int $timeout;

    /**
     * ZenService constructor.
     *
     * Initializes the service with configuration values for the base URL, model, and timeout.
     * These values are retrieved from the application's configuration files, allowing for
     * easy customization without modifying the code.
     */
    public function __construct()
    {
        $this->apiKey = (string) config('ai.providers.zen.api_key');
        $this->baseUrl = (string) config('ai.providers.zen.base_url');
        $this->model = (string) config('ai.providers.zen.model');
        $this->timeout = (int) config('ai.providers.zen.timeout');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function healthCheck(): bool
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(5)
                ->get($this->baseUrl.'/models');

            return $response->successful();
        } catch (Exception) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
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

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateContentWithHistory(
        array $history,
        string $prompt,
        array $options = []
    ): string {
        $messages = [];

        foreach ($history as $message) {
            $content = (string) ($message['content'] ?? '');

            if ($content === '') {
                continue;
            }

            $messages[] = [
                'role' => ($message['role'] ?? 'user') === 'assistant'
                            ? 'assistant' : 'user',
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
     * {@inheritDoc}
     */
    #[Override]
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
                'role' => ($message['role'] ?? 'user') === 'assistant'
                            ? 'assistant' : 'user',
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
     * Stream content based on a system prompt, conversation history, and user prompt.
     *
     * @param  string  $systemPrompt  The system prompt for content generation.
     * @param  array<int, array<string, mixed>>  $history  An array of previous messages in the conversation.
     * @param  string  $prompt  The user prompt to generate content for.
     * @param  callable(string): void  $onChunk  A callback function that is invoked for each generated text chunk.
     * @param  array<string, mixed>  $options  Additional options for content generation.
     * @return string The full generated content from Zen.
     *
     * @throws Exception If the API key or base URL is missing, or if the request fails.
     */
    public function streamContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        callable $onChunk,
        array $options = [],
    ): string {
        if ($this->apiKey === '') {
            throw new Exception('Zen API key is missing');
        }

        if ($this->baseUrl === '') {
            throw new Exception('Zen API base URL is missing');
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
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

        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['maxOutputTokens'] ?? 4096,
            'stream' => true,
        ];

        $fullText = '';

        try {
            $response = Http::withOptions(['stream' => true])
                ->acceptJson()
                ->withToken($this->apiKey)
                ->post($this->baseUrl.'/chat/completions', $payload);

            if ($response->failed()) {
                throw new Exception($this->extractErrorMessage($response));
            }

            $body = $response->toPsrResponse()->getBody();

            while (! $body->eof()) {
                $line = $this->readLine($body);

                if ($line === '' || $line === 'data: [DONE]') {
                    continue;
                }

                if (! \str_starts_with($line, 'data: ')) {
                    continue;
                }

                $data = \json_decode(\substr($line, 6), true);

                if (! \is_array($data)) {
                    continue;
                }

                $text = (string) ($data['choices'][0]['delta']['content'] ?? '');

                if ($text !== '') {
                    $fullText .= $text;
                    $onChunk($text);
                }
            }
        } catch (ConnectionException $exception) {
            throw new Exception('Zen connection failed: '.$exception->getMessage());
        }

        if ($fullText === '') {
            throw new Exception('Zen returned an empty response');
        }

        return $fullText;
    }

    /**
     * Read a line from the stream.
     */
    private function readLine(StreamInterface $stream): string
    {
        $buffer = '';

        while (! $stream->eof()) {
            $char = $stream->read(1);

            if ($char === "\n") {
                break;
            }

            $buffer .= $char;
        }

        return \trim($buffer);
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<string, mixed>  $options
     */
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

    /**
     * Extract the error message from the response.
     */
    private function extractErrorMessage(Response $response): string
    {
        $message = (string) ($response->json('error.message')
                                ?? $response->json('message')
                                ?? 'Unknown Zen API error'
        );

        return 'Zen API error ('.$response->status().'): '.$message;
    }
}
