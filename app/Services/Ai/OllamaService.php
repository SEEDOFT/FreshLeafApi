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
use function trim;

class OllamaService implements AiProviderContract
{
    private string $baseUrl;

    private string $model;

    private int $timeout;

    /**
     * OllamaService constructor.
     *
     * Initializes the service with configuration values for the base URL, model, and timeout.
     * These values are retrieved from the application's configuration files, allowing for
     * easy customization without modifying the code.
     */
    public function __construct()
    {
        $this->baseUrl = (string) config('ai.providers.ollama.base_url');
        $this->model = (string) config('ai.providers.ollama.model');
        $this->timeout = (int) config('ai.providers.ollama.timeout');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl.'/api/tags');

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
                'role' => ($message['role'] ?? 'user') === 'assistant'
                            ? 'assistant' : 'user',
                'content' => $content,
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => true,
            'options' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'num_predict' => $options['maxOutputTokens'] ?? 4096,
            ],
        ];

        $fullText = '';

        try {
            $response = Http::withOptions(['stream' => true])
                ->acceptJson()
                ->post($this->baseUrl.'/api/chat', $payload);

            if ($response->failed()) {
                throw new Exception($this->extractErrorMessage($response));
            }

            $body = $response->toPsrResponse()->getBody();

            while (! $body->eof()) {
                $line = $this->readLine($body);
                if ($line === '') {
                    continue;
                }

                $data = \json_decode($line, true);
                if (! \is_array($data)) {
                    continue;
                }

                $text = (string) ($data['message']['content'] ?? '');
                if ($text !== '') {
                    $fullText .= $text;
                    $onChunk($text);
                }

                if (($data['done'] ?? false) === true) {
                    break;
                }
            }
        } catch (ConnectionException $exception) {
            throw new Exception('Ollama connection failed: '.$exception->getMessage());
        }

        if ($fullText === '') {
            throw new Exception('Ollama returned an empty response');
        }

        return $fullText;
    }

    /**
     * Read a line from the stream.
     */
    private function readLine(StreamInterface $stream): string
    {
        $line = '';

        while (! $stream->eof()) {
            $char = $stream->read(1);
            $line .= $char;

            if ($char === "\n") {
                break;
            }
        }

        return trim($line);
    }

    /**
     * Send a chat request to the Ollama API with the given messages and options.
     * This method constructs the payload for the API request, handles the HTTP communication, and processes the response.
     * It throws exceptions for any connection issues or API errors, providing descriptive messages to help with debugging.
     *
     * @param  array<int, array<string, mixed>>  $messages  An array of messages to send to the API, each with a role and content.
     * @param  array<string, mixed>  $options  Optional parameters for the API request, such as temperature and max output tokens.
     * @return string The generated content from the API response.
     *
     * @throws Exception If there is a connection issue or if the API returns an error response.
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

    /**
     * Extract a user-friendly error message from a failed Ollama API response.
     *
     * @param  Response  $response  The HTTP response from the Ollama API.
     * @return string A formatted error message including the status code and error details.
     */
    private function extractErrorMessage(Response $response): string
    {
        $message = (string) $response->json('error', 'Unknown Ollama error');

        return 'Ollama error ('.$response->status().'): '.$message;
    }
}
