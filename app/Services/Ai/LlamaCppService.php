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

class LlamaCppService implements AiProviderContract
{
    private string $baseUrl;

    private string $model;

    private int $timeout;

    /**
     * LlamaCppService constructor.
     */
    public function __construct()
    {
        $this->baseUrl = \rtrim((string) \config('ai.providers.llama_cpp.base_url', 'http://127.0.0.1:9000'), '/');
        $this->model = (string) \config('ai.providers.llama_cpp.model', 'phi-3-mini');
        $this->timeout = (int) \config('ai.providers.llama_cpp.timeout', 120);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateContent(string $prompt, array $options = []): string
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];

        return $this->requestChat($messages, $options);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
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

        $messages[] = ['role' => 'user', 'content' => $prompt];

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
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt],
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

        return $this->requestChat($messages, $options);
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
            'max_tokens' => $options['maxOutputTokens'] ?? 2048,
            'stream' => true,
        ];

        $fullText = '';

        try {
            $response = Http::withOptions(['stream' => true])
                ->acceptJson()
                ->post($this->baseUrl.'/v1/chat/completions', $payload);

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
            throw new Exception('Llama.cpp connection failed: '.$exception->getMessage());
        }

        if ($fullText === '') {
            throw new Exception('Llama.cpp returned an empty response');
        }

        return $fullText;
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
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['maxOutputTokens'] ?? 2048,
            'stream' => false,
        ];

        try {
            $response = Http::acceptJson()
                ->timeout($this->timeout)
                ->post($this->baseUrl.'/v1/chat/completions', $payload);
        } catch (ConnectionException $exception) {
            throw new Exception('Llama.cpp connection failed: '.$exception->getMessage());
        }

        if ($response->failed()) {
            throw new Exception($this->extractErrorMessage($response));
        }

        $content = (string) $response->json('choices.0.message.content', '');

        if ($content === '') {
            throw new Exception('Llama.cpp returned an empty response');
        }

        return $content;
    }

    private function extractErrorMessage(Response $response): string
    {
        $message = (string) ($response->json('error.message') ?? $response->json('message') ?? 'Unknown Llama.cpp API error');

        return 'Llama.cpp API error ('.$response->status().'): '.$message;
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
}
