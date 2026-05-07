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

class GeminiService implements AiProviderContract
{
    private string $apiKey;

    private string $model;

    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * GeminiService constructor.
     *
     * Initializes the GeminiService with the API key and model from configuration.
     *
     * @throws Exception If the API key is missing in the configuration.
     */
    public function __construct()
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.0-flash');

        $this->apiKey = \is_string($apiKey) ? $apiKey : '';
        $this->model = \is_string($model) ? $model : 'gemini-2.0-flash';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl.'/models/'.$this->model, [
                'key' => $this->apiKey,
            ]);

            return $response->successful();
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Generate content based on a prompt without history.
     *
     * @param  string  $prompt  The user prompt to generate content for.
     * @param  array<string, mixed>  $options  Optional generation parameters:
     *                                         - temperature: float (default: 0.7)
     *                                         - topP: float (default: 0.9)
     *                                         - topK: int (default: 40)
     *                                         - maxOutputTokens: int (default: 4096)
     * @return string The generated content from Gemini.
     *
     * @throws Exception If the API key is missing, the request fails, or the response is invalid.
     */
    public function generateContent(string $prompt, array $options = []): string
    {
        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                ],
            ],
        ];

        return $this->requestContent($contents, $options);
    }

    /**
     * Generate content based on a prompt and conversation history.
     *
     * @param  array<int, array<string, mixed>>  $history  An array of previous messages in the conversation. Each message should have a 'role' (e.g., 'user' or 'assistant') and 'content'.
     * @param  string  $prompt  The user prompt to generate content for.
     * @param  array<string, mixed>  $options  Optional generation parameters:
     *                                         - temperature: float (default: 0.7)
     *                                         - topP: float (default: 0.9)
     *                                         - topK: int (default: 40)
     *                                         - maxOutputTokens: int (default: 4096)
     * @return string The generated content from Gemini.
     *
     * @throws Exception If the API key is missing, the request fails, or the response is invalid.
     */
    public function generateContentWithHistory(array $history, string $prompt, array $options = []): string
    {
        $contents = [];

        foreach ($history as $message) {
            $content = (\is_array($message) && isset($message['content']) && \is_string($message['content'])) ? $message['content'] : '';

            if ($content === '') {
                continue;
            }

            $role = (\is_array($message) && ($message['role'] ?? 'user') === 'assistant') ? 'model' : 'user';

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $content],
                ],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
            ],
        ];

        return $this->requestContent($contents, $options);
    }

    /**
     * Generate content based on a system prompt and user prompt without history.
     *
     * @param  string  $systemPrompt  The system instruction to guide the model's response.
     * @param  string  $prompt  The user prompt to generate content for.
     * @param  array<string, mixed>  $options  Optional generation parameters:
     *                                         - temperature: float (default: 0.7)
     *                                         - topP: float (default: 0.9)
     *                                         - topK: int (default: 40)
     *                                         - maxOutputTokens: int (default: 4096)
     * @return string The generated content from Gemini.
     *
     * @throws Exception If the API key is missing, the request fails, or the response is invalid.
     */
    public function generateContentWithSystemPrompt(
        string $systemPrompt,
        string $prompt,
        array $options = [],
    ): string {
        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                ],
            ],
        ];

        return $this->requestContentWithSystemInstruction($systemPrompt, $contents, $options);
    }

    /**
     * Generate content based on a system prompt, conversation history, and user prompt.
     *
     * @param  string  $systemPrompt  The system instruction to guide the model's response.
     * @param  array<int, array<string, mixed>>  $history  An array of previous messages in the conversation. Each message should have a 'role' (e.g., 'user' or 'assistant') and 'content'.
     * @param  string  $prompt  The user prompt to generate content for.
     * @param  array<string, mixed>  $options  Optional generation parameters:
     *                                         - temperature: float (default: 0.7)
     *                                         - topP: float (default: 0.9)
     *                                         - topK: int (default: 40)
     *                                         - maxOutputTokens: int (default: 4096)
     * @return string The generated content from Gemini.
     *
     * @throws Exception If the API key is missing, the request fails, or the response is invalid.
     */
    public function generateContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        array $options = [],
    ): string {
        $contents = [];

        foreach ($history as $message) {
            $content = (\is_array($message) && isset($message['content']) && \is_string($message['content'])) ? $message['content'] : '';

            if ($content === '') {
                continue;
            }

            $role = (\is_array($message) && ($message['role'] ?? 'user') === 'assistant') ? 'model' : 'user';

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $content],
                ],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
            ],
        ];

        return $this->requestContentWithSystemInstruction($systemPrompt, $contents, $options);
    }

    /**
     * Stream content based on a system prompt, conversation history, and user prompt.
     *
     * @param  string  $systemPrompt  The system instruction to guide the model's response.
     * @param  array<int, array<string, mixed>>  $history  An array of previous messages in the conversation.
     * @param  string  $prompt  The user prompt to generate content for.
     * @param  callable(string): void  $onChunk  A callback function that is invoked for each generated text chunk.
     * @param  array<string, mixed>  $options  Optional generation parameters.
     * @return string The full generated content from Gemini.
     *
     * @throws Exception If the API key is missing, the request fails, or the response is invalid.
     */
    public function streamContentWithSystemPromptAndHistory(
        string $systemPrompt,
        array $history,
        string $prompt,
        callable $onChunk,
        array $options = [],
    ): string {
        if ($this->apiKey === '') {
            throw new Exception('Gemini API key is missing');
        }

        $contents = [];

        foreach ($history as $message) {
            $content = (\is_array($message) && isset($message['content']) && \is_string($message['content'])) ? $message['content'] : '';

            if ($content === '') {
                continue;
            }

            $role = (\is_array($message) && ($message['role'] ?? 'user') === 'assistant') ? 'model' : 'user';

            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $content]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]],
        ];

        $payload = [
            'systemInstruction' => [
                'role' => 'user',
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'topP' => $options['topP'] ?? 0.9,
                'topK' => $options['topK'] ?? 40,
                'maxOutputTokens' => $options['maxOutputTokens'] ?? 4096,
            ],
        ];

        $fullText = '';

        try {
            $response = Http::withOptions(['stream' => true])
                ->acceptJson()
                ->post($this->endpointUrl('streamGenerateContent', ['alt' => 'sse']), $payload);

            if ($response->failed()) {
                throw new Exception($this->extractErrorMessage($response));
            }

            $body = $response->toPsrResponse()->getBody();

            while (! $body->eof()) {
                $line = $this->readLine($body);

                if (! \str_starts_with($line, 'data: ')) {
                    continue;
                }

                $data = \json_decode(\substr($line, 6), true);

                if (! \is_array($data)) {
                    continue;
                }

                $candidates = $data['candidates'] ?? null;
                if (! \is_array($candidates) || ! isset($candidates[0]) || ! \is_array($candidates[0])) {
                    continue;
                }

                $candidate = $candidates[0];
                $content = $candidate['content'] ?? null;
                $parts = \is_array($content) ? ($content['parts'] ?? null) : null;

                if (\is_array($parts) && isset($parts[0]) && \is_array($parts[0]) && isset($parts[0]['text']) && \is_string($parts[0]['text'])) {
                    $text = $parts[0]['text'];
                    $fullText .= $text;
                    $onChunk($text);
                }
            }
        } catch (ConnectionException $exception) {
            throw new Exception('Gemini API connection failed: '.$exception->getMessage());
        }

        if ($fullText === '') {
            throw new Exception('Gemini returned an empty response');
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
     * Make a request to the Gemini API with the given contents and options.
     * Handles the API response and extracts the generated text.
     *
     * @param  array<int, array<string, mixed>>  $contents
     * @param  array<string, mixed>  $options
     *
     * @throws Exception If the API key is missing, the request fails, or the response is invalid.
     */
    private function requestContent(array $contents, array $options): string
    {
        if ($this->apiKey === '') {
            throw new Exception('Gemini API key is missing');
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'topP' => $options['topP'] ?? 0.9,
                'topK' => $options['topK'] ?? 40,
                'maxOutputTokens' => $options['maxOutputTokens'] ?? 4096,
            ],
        ];

        try {
            $response = Http::acceptJson()
                ->connectTimeout(10)
                ->timeout(40)
                ->retry([200, 500, 1000], throw: false)
                ->post($this->endpointUrl(), $payload);
        } catch (ConnectionException $exception) {
            throw new Exception('Gemini API connection failed: '.$exception->getMessage());
        }

        if ($response->failed()) {
            throw new Exception($this->extractErrorMessage($response));
        }

        $parts = $response->json('candidates.0.content.parts', []);

        if (! \is_array($parts) || $parts === []) {
            throw new Exception('No text content in Gemini response');
        }

        $text = \collect($parts)
            ->map(static function (mixed $part): string {
                return (\is_array($part) && isset($part['text']) && \is_string($part['text'])) ? $part['text'] : '';
            })
            ->implode('');

        if ($text === '') {
            throw new Exception('Gemini returned an empty response');
        }

        return $text;
    }

    /**
     * Make a request to the Gemini API with a system instruction and the given contents and options.
     * Handles the API response and extracts the generated text.
     *
     * @param  string  $systemPrompt  The system instruction to guide the model's response.
     * @param  array<int, array<string, mixed>>  $contents  The conversation contents.
     * @param  array<string, mixed>  $options  Optional generation parameters.
     *
     * @throws Exception If the API key is missing, the request fails, or the response is invalid.
     */
    private function requestContentWithSystemInstruction(
        string $systemPrompt,
        array $contents,
        array $options,
    ): string {
        if ($this->apiKey === '') {
            throw new Exception('Gemini API key is missing');
        }

        $payload = [
            'systemInstruction' => [
                'role' => 'user',
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'topP' => $options['topP'] ?? 0.9,
                'topK' => $options['topK'] ?? 40,
                'maxOutputTokens' => $options['maxOutputTokens'] ?? 4096,
            ],
        ];

        try {
            $response = Http::acceptJson()
                ->connectTimeout(10)
                ->timeout(40)
                ->retry([200, 500, 1000], throw: false)
                ->post($this->endpointUrl(), $payload);
        } catch (ConnectionException $exception) {
            throw new Exception('Gemini API connection failed: '.$exception->getMessage());
        }

        if ($response->failed()) {
            throw new Exception($this->extractErrorMessage($response));
        }

        $parts = $response->json('candidates.0.content.parts', []);

        if (! \is_array($parts) || $parts === []) {
            throw new Exception('No text content in Gemini response');
        }

        $text = \collect($parts)
            ->map(static function (mixed $part): string {
                return (\is_array($part) && isset($part['text']) && \is_string($part['text'])) ? $part['text'] : '';
            })
            ->implode('');

        if ($text === '') {
            throw new Exception('Gemini returned an empty response');
        }

        return $text;
    }

    /**
     * Construct the full endpoint URL for the Gemini API request.
     *
     * @param  string  $method  The API method to call (default: generateContent).
     * @param  array<string, string>  $params  Optional query parameters.
     * @return string The full URL to send requests to.
     */
    private function endpointUrl(string $method = 'generateContent', array $params = []): string
    {
        $queryParams = \array_merge($params, ['key' => $this->apiKey]);

        return \sprintf(
            '%s/models/%s:%s?%s',
            $this->baseUrl,
            $this->model,
            $method,
            \http_build_query($queryParams)
        );
    }

    /**
     * Extract a user-friendly error message from a failed Gemini API response.
     *
     * @param  Response  $response  The HTTP response from the Gemini API.
     * @return string A formatted error message including the status code and error details.
     */
    private function extractErrorMessage(Response $response): string
    {
        $message = $response->json('error.message');

        return 'Gemini API error ('.$response->status().'): '.(\is_string($message) ? $message : 'Unknown Gemini API error');
    }
}
