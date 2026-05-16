<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Ai\AiService;
use App\Services\Ai\GeminiService;
use App\Services\Ai\OllamaService;
use App\Services\Ai\ZenService;
use Exception;
use Mockery;
use Tests\TestCase;

class AiServiceProviderSelectionTest extends TestCase
{
    public function test_it_uses_only_the_configured_provider_without_fallbacks(): void
    {
        \config(['ai.default' => 'zen']);

        $geminiService = Mockery::mock(GeminiService::class);
        $ollamaService = Mockery::mock(OllamaService::class);
        $zenService = Mockery::mock(ZenService::class);

        $geminiService->shouldNotReceive('generateContent');
        $ollamaService->shouldNotReceive('generateContent');
        $zenService->shouldReceive('generateContent')
            ->once()
            ->with('Hello', [])
            ->andThrow(new Exception('Zen connection failed.'));

        $service = new AiService($geminiService, $ollamaService, $zenService);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Zen connection failed.');

        $service->generateContent('Hello');
    }

    public function test_it_rejects_unsupported_configured_provider(): void
    {
        \config(['ai.default' => 'llama_cpp']);

        $service = new AiService(
            Mockery::mock(GeminiService::class),
            Mockery::mock(OllamaService::class),
            Mockery::mock(ZenService::class),
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Configured AI provider [llama_cpp] is not supported.');

        $service->generateContent('Hello');
    }
}
