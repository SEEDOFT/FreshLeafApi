<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\AiMessageChunk;
use App\Events\AiMessageCompleted;
use App\Events\AiMessageStarted;
use App\Jobs\ProcessAiChatMessageJob;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use App\Services\Ai\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class AiChatStreamingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * This test verifies that the ProcessAiChatMessageJob correctly streams AI response chunks in real-time,
     * dispatching the appropriate events and updating the database record as expected.
     */
    public function test_ai_chat_job_streams_messages_in_real_time(): void
    {
        // 1. Setup Data
        Event::fake();
        $user = User::factory()->create();
        $session = AiChatSession::create([
            'user_id' => $user->id,
            'session_id' => 'test-session-123',
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

        // 2. Mock AI Service to simulate streaming
        $mockAiService = Mockery::mock(AiService::class);
        $this->app->instance(AiService::class, $mockAiService);

        // We simulate 3 chunks being sent from the AI
        $chunks = ['Hello', ' world', '!'];
        $fullText = implode('', $chunks);

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andReturnUsing(static function ($systemPrompt, $history, $prompt, $onChunk, $options) use ($chunks, $fullText) {
                foreach ($chunks as $chunk) {
                    $onChunk($chunk);
                }

                return $fullText;
            });

        // 3. Execute Job
        $job = new ProcessAiChatMessageJob(
            userId: $user->id,
            sessionId: $session->session_id,
            messageId: (string) $assistantMessage->message_id,
            prompt: 'Hi',
            language: 'en'
        );

        app()->call([$job, 'handle']);

        // 4. Assert Events were fired correctly
        Event::assertDispatched(AiMessageStarted::class);

        // Assert AiMessageChunk was dispatched for the buffered chunks
        Event::assertDispatched(AiMessageChunk::class, static function ($event) use ($fullText) {
            return $event->role === 'assistant' && $event->textChunk === $fullText;
        });

        Event::assertDispatched(AiMessageCompleted::class, static function ($event) use ($fullText) {
            return $event->fullText === $fullText;
        });

        // 5. Assert Database is updated
        $assistantMessage->refresh();
        $this->assertEquals($fullText, $assistantMessage->content);
        $this->assertEquals('done', $assistantMessage->status);
    }

    /**
     * This test verifies that the ProcessAiChatMessageJob respects the 100ms buffering limit before emitting chunks,
     * ensuring that chunks are emitted in a timely manner and not delayed unnecessarily.
     */
    public function test_ai_chat_job_respects_100ms_buffering_limit(): void
    {
        Event::fake();
        $user = User::factory()->create();
        $session = AiChatSession::create([
            'user_id' => $user->id,
            'session_id' => 'test-buffer-123',
            'title' => 'Test Buffer',
        ]);

        $assistantMessage = AiChatMessage::create([
            'ai_chat_session_id' => $session->id,
            'message_id' => 'msg-'.uniqid(),
            'role' => 'assistant',
            'content' => '',
            'status' => 'processing',
            'sequence' => 10,
        ]);

        $mockAiService = Mockery::mock(AiService::class);
        $this->app->instance(AiService::class, $mockAiService);

        $mockAiService->shouldReceive('streamContentWithSystemPromptAndHistory')
            ->once()
            ->andReturnUsing(static function ($systemPrompt, $history, $prompt, $onChunk) {
                // Sleep first to ensure the first chunk trigger the 100ms threshold
                usleep(110000);
                $onChunk('Chunk 1');

                // Sleep again to ensure the second chunk triggers another 100ms threshold
                usleep(110000);
                $onChunk('Chunk 2');

                return 'Chunk 1Chunk 2';
            });

        $job = new ProcessAiChatMessageJob(
            userId: $user->id,
            sessionId: $session->session_id,
            messageId: (string) $assistantMessage->message_id,
            prompt: 'Hi',
            language: 'en'
        );

        app()->call([$job, 'handle']);

        // Should have 2 chunk events: one from the time trigger, one from the final flush
        // Wait, in this case because each triggers the 100ms, they both emit inside onChunk.
        Event::assertDispatched(AiMessageChunk::class, 2);

        Event::assertDispatched(AiMessageChunk::class, static function ($event) {
            return $event->textChunk === 'Chunk 1' && $event->sequence === 11;
        });

        Event::assertDispatched(AiMessageChunk::class, static function ($event) {
            return $event->textChunk === 'Chunk 2' && $event->sequence === 12;
        });
    }
}
