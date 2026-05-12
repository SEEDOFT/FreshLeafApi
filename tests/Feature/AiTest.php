<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ProcessAiChatMessageJob;
use App\Models\AiChatSession;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AiTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserStatus::upsert([
            ['id' => UserStatus::ACTIVE, 'code' => 'ACTIVE', 'name' => 'Active'],
            ['id' => UserStatus::INACTIVE, 'code' => 'INACTIVE', 'name' => 'Inactive'],
            ['id' => UserStatus::DELETED, 'code' => 'DELETED', 'name' => 'Deleted'],
        ], ['id'], ['code', 'name']);

        UserType::upsert([
            ['id' => UserType::CONSUMER_ID, 'code' => 'USER', 'name' => 'User'],
            ['id' => UserType::VENDOR, 'code' => 'VENDOR', 'name' => 'Vendor'],
            ['id' => UserType::ADMIN, 'code' => 'ADMIN', 'name' => 'Admin'],
        ], ['id'], ['code', 'name']);
    }

    public function test_can_create_ai_chat_session(): void
    {
        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER_ID,
        ]);

        $token = $user->createToken('ai_session')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ai/chat/sessions', [
                'title' => 'Support Session',
                'session_id' => 'ai-test-session-001',
            ]);

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.session_id', 'ai-test-session-001')
            ->assertJsonPath('data.title', 'Support Session');

        $this->assertDatabaseHas('ai_chat_sessions', [
            'user_id' => $user->id,
            'session_id' => 'ai-test-session-001',
            'title' => 'Support Session',
        ]);
    }

    public function test_store_message_creates_records_and_dispatches_ai_job(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER_ID,
        ]);

        $session = AiChatSession::query()->create([
            'user_id' => $user->id,
            'session_id' => 'ai-test-session-002',
            'title' => 'Queue Test',
            'last_message_at' => now(),
        ]);

        $token = $user->createToken('ai_message')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ai/chat/messages', [
                'session_id' => $session->session_id,
                'message' => 'Can you recommend fresh vegetables?',
                'temperature' => 0.5,
                'max_output_tokens' => 300,
            ]);

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.session_id', $session->session_id)
            ->assertJsonPath('data.status', 'queued');

        $this->assertDatabaseHas('ai_chat_messages', [
            'ai_chat_session_id' => $session->id,
            'user_id' => $user->id,
            'role' => 'user',
            'content' => 'Can you recommend fresh vegetables?',
            'status' => 'done',
        ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'ai_chat_session_id' => $session->id,
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => '',
            'status' => 'streaming',
        ]);

        Queue::assertPushed(ProcessAiChatMessageJob::class, function (ProcessAiChatMessageJob $job) use ($user, $session): bool {
            return $job->userId === $user->id
                && $job->sessionId === $session->session_id
                && $job->prompt === 'Can you recommend fresh vegetables?'
                && $job->temperature === 0.5
                && $job->maxOutputTokens === 300;
        });

        Queue::assertPushedOn('ai-stream', ProcessAiChatMessageJob::class);
    }
}
