<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Ai\AiService;
use App\Services\Ai\GeminiService;
use App\Services\Ai\LlamaCppService;
use App\Services\Ai\OllamaService;
use Exception;
use Mockery;
use Tests\TestCase;

class AiServiceProviderSelectionTest extends TestCase
{
    public function test_it_uses_only_the_configured_provider_without_fallbacks(): void
    {
        \config(['ai.default' => 'llama_cpp']);

        $geminiService = Mockery::mock(GeminiService::class);
        $ollamaService = Mockery::mock(OllamaService::class);
        $llamaCppService = Mockery::mock(LlamaCppService::class);

        $geminiService->shouldNotReceive('generateContent');
        $ollamaService->shouldNotReceive('generateContent');
        $llamaCppService->shouldReceive('generateContent')
            ->once()
            ->with('Hello', [])
            ->andThrow(new Exception('Llama.cpp connection failed.'));

        $service = new AiService($geminiService, $ollamaService, $llamaCppService);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Llama.cpp connection failed.');

        $service->generateContent('Hello');
    }

    public function test_it_rejects_unsupported_configured_provider(): void
    {
        \config(['ai.default' => 'zen']);

        $service = new AiService(
            Mockery::mock(GeminiService::class),
            Mockery::mock(OllamaService::class),
            Mockery::mock(LlamaCppService::class),
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Configured AI provider [zen] is not supported.');

        $service->generateContent('Hello');
    }
}
