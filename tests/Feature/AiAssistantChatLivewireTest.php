<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\AiAssistantChat;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AiAssistantChatLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_switch_chat_sessions(): void
    {
        $user = User::factory()->create();

        $firstSession = AiChatSession::create([
            'user_id' => $user->id,
            'session_id' => 'session-first',
            'title' => 'First Session',
            'last_message_at' => now()->subMinutes(6),
        ]);

        AiChatMessage::create([
            'ai_chat_session_id' => $firstSession->id,
            'message_id' => 'msg-first',
            'role' => 'assistant',
            'content' => 'First session message',
            'status' => 'done',
            'sequence' => 1,
        ]);

        $secondSession = AiChatSession::create([
            'user_id' => $user->id,
            'session_id' => 'session-second',
            'title' => 'Second Session',
            'last_message_at' => now()->subMinute(),
        ]);

        AiChatMessage::create([
            'ai_chat_session_id' => $secondSession->id,
            'message_id' => 'msg-second',
            'role' => 'assistant',
            'content' => 'Second session message',
            'status' => 'done',
            'sequence' => 1,
        ]);

        $this->actingAs($user);
        $component = Livewire::test(AiAssistantChat::class);

        $component->assertSet('activeDbSessionId', $secondSession->id);

        $originalActiveSession = (int) $component->get('activeDbSessionId');

        $component->call('startNewChat');

        $newActiveSession = (int) $component->get('activeDbSessionId');

        $this->assertNotSame($originalActiveSession, $newActiveSession);
        $this->assertDatabaseHas('ai_chat_sessions', [
            'id' => $newActiveSession,
            'user_id' => $user->id,
        ]);

        $component->call('switchSession', $firstSession->id)
            ->assertSet('activeDbSessionId', $firstSession->id);

        /** @var array<array{content: string}> $messages */
        $messages = $component->get('messages');

        $this->assertSame('First session message', $messages[0]['content']);
    }

    public function test_stream_event_handlers_update_assistant_message_state(): void
    {
        $user = User::factory()->create();

        $session = AiChatSession::create([
            'user_id' => $user->id,
            'session_id' => 'stream-session',
            'title' => 'Stream Session',
            'last_message_at' => now(),
        ]);

        AiChatMessage::create([
            'ai_chat_session_id' => $session->id,
            'message_id' => 'assistant-stream',
            'role' => 'assistant',
            'content' => '',
            'status' => 'processing',
            'sequence' => 1,
        ]);

        $this->actingAs($user);
        $component = Livewire::test(AiAssistantChat::class);

        $pendingSinceUnix = time() - 20;
        $component->set('pendingSinceUnix', $pendingSinceUnix);

        $component->call('handleChunk', [
            'message_id' => 'assistant-stream',
            'text_chunk' => 'Hello',
        ])
            ->assertSet('isTyping', true)
            ->assertSet('pendingSinceUnix', $pendingSinceUnix);

        $component->call('handleChunk', [
            'message_id' => 'assistant-stream',
            'text_chunk' => ' world',
        ]);

        /** @var array<array{message_id?: string, content: string}> $messagesAfterChunk */
        $messagesAfterChunk = $component->get('messages');
        $chunkedMessage = collect($messagesAfterChunk)->firstWhere('message_id', 'assistant-stream');

        $this->assertNotNull($chunkedMessage);
        $this->assertSame('Hello world', $chunkedMessage['content']);

        $component->call('handleCompleted', [
            'message_id' => 'assistant-stream',
            'full_text' => 'Hello world!',
        ])->assertSet('isTyping', false);

        /** @var array<array{message_id?: string, content: string}> $messagesAfterDone */
        $messagesAfterDone = $component->get('messages');
        $doneMessage = collect($messagesAfterDone)->firstWhere('message_id', 'assistant-stream');

        $this->assertNotNull($doneMessage);
        $this->assertSame('Hello world!', $doneMessage['content']);
    }

    public function test_pending_message_recovers_with_polling_fallback_when_realtime_is_down(): void
    {
        $user = User::factory()->create();

        $session = AiChatSession::create([
            'user_id' => $user->id,
            'session_id' => 'fallback-session',
            'title' => 'Fallback Session',
            'last_message_at' => now(),
        ]);

        $assistantMessage = AiChatMessage::create([
            'ai_chat_session_id' => $session->id,
            'message_id' => 'assistant-fallback',
            'role' => 'assistant',
            'content' => '',
            'status' => 'processing',
            'sequence' => 1,
        ]);

        $this->actingAs($user);
        $component = Livewire::test(AiAssistantChat::class)
            ->set('pendingAssistantMessageId', 'assistant-fallback')
            ->set('pendingSinceUnix', time() - 12)
            ->set('isTyping', true)
            ->set('isRealtimeConnected', false);

        $assistantMessage->update([
            'content' => 'Recovered response content',
            'status' => 'done',
        ]);

        $component->call('syncPendingResponse')
            ->assertSet('isTyping', false)
            ->assertSet('pendingAssistantMessageId', null);

        /** @var array<array{message_id?: string, content: string}> $messages */
        $messages = $component->get('messages');
        $recoveredMessage = collect($messages)->firstWhere('message_id', 'assistant-fallback');

        $this->assertNotNull($recoveredMessage);
        $this->assertSame('Recovered response content', $recoveredMessage['content']);
    }

    public function test_pending_message_displays_partial_database_content_while_processing(): void
    {
        $user = User::factory()->create();

        $session = AiChatSession::create([
            'user_id' => $user->id,
            'session_id' => 'partial-session',
            'title' => 'Partial Session',
            'last_message_at' => now(),
        ]);

        $assistantMessage = AiChatMessage::create([
            'ai_chat_session_id' => $session->id,
            'message_id' => 'assistant-partial',
            'role' => 'assistant',
            'content' => '',
            'status' => 'processing',
            'sequence' => 1,
        ]);

        $this->actingAs($user);
        $component = Livewire::test(AiAssistantChat::class)
            ->set('pendingAssistantMessageId', 'assistant-partial')
            ->set('pendingSinceUnix', time() - 12)
            ->set('isTyping', true)
            ->set('isRealtimeConnected', false);

        $assistantMessage->update([
            'content' => 'Partial response content',
            'status' => 'processing',
        ]);

        $component->call('syncPendingResponse')
            ->assertSet('isTyping', true)
            ->assertSet('pendingAssistantMessageId', 'assistant-partial');

        /** @var array<array{message_id?: string, content: string, status?: string}> $messages */
        $messages = $component->get('messages');
        $partialMessage = collect($messages)->firstWhere('message_id', 'assistant-partial');

        $this->assertNotNull($partialMessage);
        $this->assertSame('Partial response content', $partialMessage['content']);
        $this->assertSame('processing', $partialMessage['status'] ?? null);
    }

    public function test_pending_message_times_out_and_marks_failed_in_fallback_mode(): void
    {
        $user = User::factory()->create();

        $session = AiChatSession::create([
            'user_id' => $user->id,
            'session_id' => 'timeout-session',
            'title' => 'Timeout Session',
            'last_message_at' => now(),
        ]);

        AiChatMessage::create([
            'ai_chat_session_id' => $session->id,
            'message_id' => 'assistant-timeout',
            'role' => 'assistant',
            'content' => '',
            'status' => 'processing',
            'sequence' => 1,
        ]);

        $this->actingAs($user);
        $component = Livewire::test(AiAssistantChat::class)
            ->set('pendingAssistantMessageId', 'assistant-timeout')
            ->set('pendingSinceUnix', time() - 301)
            ->set('isTyping', true)
            ->set('isRealtimeConnected', false);

        $component->call('syncPendingResponse')
            ->assertSet('isTyping', false)
            ->assertSet('pendingAssistantMessageId', null);

        /** @var array<array{message_id?: string, content: string, status?: string}> $messages */
        $messages = $component->get('messages');
        $timeoutMessage = collect($messages)->firstWhere('message_id', 'assistant-timeout');

        $this->assertNotNull($timeoutMessage);
        $this->assertSame('failed', $timeoutMessage['status'] ?? null);
        $this->assertStringContainsString('timed out', $timeoutMessage['content']);
    }
}
