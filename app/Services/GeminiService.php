<?php

namespace App\Services;

use App\Services\Contracts\AiProviderContract;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GeminiService implements AiProviderContract
{
    private string $apiKey;

    private string $model;

    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.api_key');
        $this->model = (string) config('services.gemini.model', 'gemini-2.0-flash');
    }

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

    public function generateContentWithHistory(array $history, string $prompt, array $options = []): string
    {
        $contents = [];

        foreach ($history as $message) {
            $content = (string) ($message['content'] ?? '');

            if ($content === '') {
                continue;
            }

            $role = ($message['role'] ?? 'user') === 'assistant' ? 'model' : 'user';

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
                'maxOutputTokens' => $options['maxOutputTokens'] ?? 1024,
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

        if (! is_array($parts) || $parts === []) {
            throw new Exception('No text content in Gemini response');
        }

        $text = collect($parts)
            ->map(fn (mixed $part): string => is_array($part) ? (string) ($part['text'] ?? '') : '')
            ->implode('');

        if ($text === '') {
            throw new Exception('Gemini returned an empty response');
        }

        return $text;
    }

    private function endpointUrl(): string
    {
        return sprintf(
            '%s/models/%s:generateContent?key=%s',
            $this->baseUrl,
            $this->model,
            urlencode($this->apiKey)
        );
    }

    private function extractErrorMessage(Response $response): string
    {
        $message = (string) $response->json('error.message', 'Unknown Gemini API error');

        return 'Gemini API error ('.$response->status().'): '.$message;
    }
}
