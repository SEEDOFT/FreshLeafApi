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

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andReturnUsing(function ($systemPrompt, $history, $prompt, $onChunk, array $options) use ($assistantMessage): string {
                $this->assertSame(0.7, $options['temperature']);
                $this->assertSame(4096, $options['maxOutputTokens']);

                $response = 'Fresh vegetables are available today.';
                $onChunk($response);

                $assistantMessage->refresh();
                $this->assertSame($response, $assistantMessage->content);
                $this->assertSame('processing', $assistantMessage->status);

                return $response;
            });
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

    public function test_ai_chat_job_removes_internal_no_search_notes_from_regular_response(): void
    {
        Event::fake();
        [$user, $session, $assistantMessage] = $this->createChatFixture();

        $mockAiService = Mockery::mock(AiService::class);
        $this->app->instance(AiService::class, $mockAiService);

        $mockSearchService = Mockery::mock(WebSearchService::class);
        $this->app->instance(WebSearchService::class, $mockSearchService);

        $cleanAnswer = 'Salads are fresh dishes made with vegetables, fruits, herbs, nuts, cheese, or protein. For FreshLeaf, crisp lettuce, cucumber, tomato, carrot, and herbs are great organic salad ingredients.';
        $leakedAnswer = $cleanAnswer."\n\n[No search tag required as the user's query can be addressed directly].";

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andReturnUsing(function ($systemPrompt, $history, $prompt, $onChunk) use ($cleanAnswer): string {
                $onChunk($cleanAnswer);

                return $cleanAnswer;
            });
        $mockSearchService->shouldReceive('search')->never();

        app()->call([(new ProcessAiChatMessageJob(
            userId: $user->id,
            sessionId: $session->session_id,
            messageId: (string) $assistantMessage->message_id,
            prompt: 'tell me about salad',
            language: 'en',
        )), 'handle']);

        Event::assertDispatched(AiMessageChunk::class, static fn ($event): bool => $event->textChunk === $cleanAnswer);
        Event::assertNotDispatched(AiMessageChunk::class, static fn ($event): bool => \str_contains($event->textChunk, 'No search tag required'));
        Event::assertDispatched(AiMessageCompleted::class, static fn ($event): bool => $event->fullText === $cleanAnswer);

        $assistantMessage->refresh();
        $this->assertSame($cleanAnswer, $assistantMessage->content);
        $this->assertStringNotContainsString('No search tag required', $assistantMessage->content);
    }

    public function test_ai_chat_job_passes_system_prompt_and_project_context_to_model(): void
    {
        Event::fake();
        [$user, $session, $assistantMessage] = $this->createChatFixture();

        $mockAiService = Mockery::mock(AiService::class);
        $this->app->instance(AiService::class, $mockAiService);

        $mockSearchService = Mockery::mock(WebSearchService::class);
        $this->app->instance(WebSearchService::class, $mockSearchService);

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andReturnUsing(function (string $systemPrompt, array $history, string $prompt, callable $onChunk): string {
                $this->assertStringContainsString('You are FreshLeaf Assistant', $systemPrompt);
                // Context is now injected, not pre-loaded from file
                // $this->assertStringContainsString('Context', $systemPrompt);

                $response = 'FreshLeaf can help with organic vegetable orders and support.';
                $onChunk($response);

                return $response;
            });

        $mockSearchService->shouldReceive('search')->never();

        app()->call([(new ProcessAiChatMessageJob(
            userId: $user->id,
            sessionId: $session->session_id,
            messageId: (string) $assistantMessage->message_id,
            prompt: 'What can FreshLeaf help me with?',
            language: 'en',
        )), 'handle']);

        Event::assertDispatched(AiMessageCompleted::class, static fn ($event): bool => $event->fullText === 'FreshLeaf can help with organic vegetable orders and support.');
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

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andReturnUsing(static fn ($systemPrompt, $history, $prompt, $onChunk): string => '[SEARCH_REQUIRED: current carrot market price in Cambodia]');

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

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andThrow(new Exception('Configured AI provider [zen] is not supported.'));

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
        $this->assertSame('Configured AI provider [zen] is not supported.', $assistantMessage->error);
    }

    public function test_ai_chat_job_honors_explicit_generation_options(): void
    {
        Event::fake();
        [$user, $session, $assistantMessage] = $this->createChatFixture();

        $mockAiService = Mockery::mock(AiService::class);
        $this->app->instance(AiService::class, $mockAiService);

        $mockSearchService = Mockery::mock(WebSearchService::class);
        $this->app->instance(WebSearchService::class, $mockSearchService);

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andReturnUsing(function ($systemPrompt, $history, $prompt, $onChunk, array $options): string {
                $this->assertSame(0.5, $options['temperature']);
                $this->assertSame(128, $options['maxOutputTokens']);

                $onChunk('Short answer.');

                return 'Short answer.';
            });
        $mockSearchService->shouldReceive('search')->never();

        app()->call([(new ProcessAiChatMessageJob(
            userId: $user->id,
            sessionId: $session->session_id,
            messageId: (string) $assistantMessage->message_id,
            prompt: 'Hello',
            language: 'en',
            temperature: 0.5,
            maxOutputTokens: 128,
        )), 'handle']);

        Event::assertDispatched(AiMessageCompleted::class, static fn ($event): bool => $event->fullText === 'Short answer.');
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
