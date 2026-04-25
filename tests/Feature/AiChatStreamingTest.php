<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\AiMessageChunk;
use App\Events\AiMessageCompleted;
use App\Events\AiMessageFailed;
use App\Events\AiMessageStarted;
use App\Jobs\ProcessAiChatMessageJob;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use App\Services\Ai\AiService;
use App\Services\Ai\WebSearchService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class AiChatStreamingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_chat_job_emits_regular_response_without_search(): void
    {
        Event::fake();
        [$user, $session, $assistantMessage] = $this->createChatFixture();

        $mockAiService = Mockery::mock(AiService::class);
        $this->app->instance(AiService::class, $mockAiService);

        $mockSearchService = Mockery::mock(WebSearchService::class);
        $this->app->instance(WebSearchService::class, $mockSearchService);

        $mockAiService->shouldReceive('generateContentWithSystemPromptAndHistory')
            ->once()
            ->andReturn('Fresh vegetables are available today.');

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')->never();
        $mockSearchService->shouldReceive('search')->never();

        app()->call([(new ProcessAiChatMessageJob(
            userId: $user->id,
            sessionId: $session->session_id,
            messageId: (string) $assistantMessage->message_id,
            prompt: 'Can you recommend fresh vegetables?',
            language: 'en',
        )), 'handle']);

        Event::assertDispatched(AiMessageStarted::class);
        Event::assertDispatched(AiMessageChunk::class, static fn ($event): bool => $event->textChunk === 'Fresh vegetables are available today.');
        Event::assertDispatched(AiMessageCompleted::class, static fn ($event): bool => $event->fullText === 'Fresh vegetables are available today.');

        $assistantMessage->refresh();
        $this->assertSame('Fresh vegetables are available today.', $assistantMessage->content);
        $this->assertSame('done', $assistantMessage->status);
    }

    public function test_live_query_searches_before_asking_model_for_final_answer(): void
    {
        Event::fake();
        \config(['ai.web_search.live_query_keywords' => ['weather']]);
        [$user, $session, $assistantMessage] = $this->createChatFixture();

        $mockAiService = Mockery::mock(AiService::class);
        $this->app->instance(AiService::class, $mockAiService);

        $mockSearchService = Mockery::mock(WebSearchService::class);
        $this->app->instance(WebSearchService::class, $mockSearchService);

        $mockAiService->shouldReceive('generateContentWithSystemPromptAndHistory')->never();
        $mockSearchService->shouldReceive('search')
            ->once()
            ->with('what is the weather in Phnom Penh today?')
            ->andReturn('Phnom Penh weather is warm and cloudy.');

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andReturnUsing(function ($systemPrompt, $history, $prompt, $onChunk): string {
                $this->assertStringContainsString('Phnom Penh weather is warm and cloudy.', $prompt);
                $onChunk('It is warm and cloudy in Phnom Penh.');

                return 'It is warm and cloudy in Phnom Penh.';
            });

        app()->call([(new ProcessAiChatMessageJob(
            userId: $user->id,
            sessionId: $session->session_id,
            messageId: (string) $assistantMessage->message_id,
            prompt: 'what is the weather in Phnom Penh today?',
            language: 'en',
        )), 'handle']);

        Event::assertDispatched(AiMessageChunk::class, static fn ($event): bool => str_contains($event->textChunk, 'Accessing internet'));
        Event::assertDispatched(AiMessageChunk::class, static fn ($event): bool => $event->textChunk === 'It is warm and cloudy in Phnom Penh.');
        Event::assertDispatched(AiMessageCompleted::class, static fn ($event): bool => $event->fullText === 'It is warm and cloudy in Phnom Penh.');
    }

    public function test_search_required_tag_still_triggers_web_search(): void
    {
        Event::fake();
        \config(['ai.web_search.live_query_keywords' => []]);
        [$user, $session, $assistantMessage] = $this->createChatFixture();

        $mockAiService = Mockery::mock(AiService::class);
        $this->app->instance(AiService::class, $mockAiService);

        $mockSearchService = Mockery::mock(WebSearchService::class);
        $this->app->instance(WebSearchService::class, $mockSearchService);

        $mockAiService->shouldReceive('generateContentWithSystemPromptAndHistory')
            ->once()
            ->andReturn('[SEARCH_REQUIRED: current carrot market price in Cambodia]');

        $mockSearchService->shouldReceive('search')
            ->once()
            ->with('current carrot market price in Cambodia')
            ->andReturn('Carrot prices are 2 USD per kg.');

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andReturnUsing(function ($systemPrompt, $history, $prompt, $onChunk): string {
                $this->assertStringContainsString('Carrot prices are 2 USD per kg.', $prompt);
                $onChunk('Carrots are about 2 USD per kg.');

                return 'Carrots are about 2 USD per kg.';
            });

        app()->call([(new ProcessAiChatMessageJob(
            userId: $user->id,
            sessionId: $session->session_id,
            messageId: (string) $assistantMessage->message_id,
            prompt: 'How much are carrots right now?',
            language: 'en',
        )), 'handle']);

        Event::assertDispatched(AiMessageChunk::class, static fn ($event): bool => str_contains($event->textChunk, 'Accessing internet'));
        Event::assertDispatched(AiMessageCompleted::class, static fn ($event): bool => $event->fullText === 'Carrots are about 2 USD per kg.');
    }

    public function test_ai_provider_failure_marks_message_failed(): void
    {
        Event::fake();
        [$user, $session, $assistantMessage] = $this->createChatFixture();

        $mockAiService = Mockery::mock(AiService::class);
        $this->app->instance(AiService::class, $mockAiService);

        $mockSearchService = Mockery::mock(WebSearchService::class);
        $this->app->instance(WebSearchService::class, $mockSearchService);

        $mockAiService->shouldReceive('generateContentWithSystemPromptAndHistory')
            ->once()
            ->andThrow(new Exception('No configured AI provider available.'));

        $mockSearchService->shouldReceive('search')->never();

        app()->call([(new ProcessAiChatMessageJob(
            userId: $user->id,
            sessionId: $session->session_id,
            messageId: (string) $assistantMessage->message_id,
            prompt: 'Hello',
            language: 'en',
        )), 'handle']);

        Event::assertDispatched(AiMessageFailed::class);
        Event::assertNotDispatched(AiMessageCompleted::class);

        $assistantMessage->refresh();
        $this->assertSame('failed', $assistantMessage->status);
        $this->assertSame('No configured AI provider is available. Check AI_PROVIDER and AI_FALLBACK_PROVIDERS.', $assistantMessage->error);
    }

    /**
     * @return array{0: User, 1: AiChatSession, 2: AiChatMessage}
     */
    private function createChatFixture(): array
    {
        $user = User::factory()->create();
        $session = AiChatSession::create([
            'user_id' => $user->id,
            'session_id' => 'test-session-'.uniqid(),
            'title' => 'Test Stream',
        ]);

        $assistantMessage = AiChatMessage::create([
            'ai_chat_session_id' => $session->id,
            'message_id' => 'msg-'.uniqid(),
            'role' => 'assistant',
            'content' => '',
            'status' => 'processing',
            'sequence' => 0,
        ]);

        return [$user, $session, $assistantMessage];
    }
}
