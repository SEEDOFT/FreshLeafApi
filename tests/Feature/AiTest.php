<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ProcessAiChatMessageJob;
use App\Models\AiChatSession;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Services\Ai\AiService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class AiTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserStatus::upsert([
            ['id' => UserStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
            ['id' => UserStatus::INACTIVE_ID, 'name_en' => 'Inactive', 'name_km' => 'អសកម្ម'],
            ['id' => UserStatus::DELETED_ID, 'name_en' => 'Deleted', 'name_km' => 'បានលុប'],
        ], ['id'], ['name_en', 'name_km']);

        UserType::upsert([
            ['id' => UserType::CONSUMER_ID, 'name_en' => 'User', 'name_km' => 'អ្នកប្រើប្រាស់'],
            ['id' => UserType::VENDOR_ID, 'name_en' => 'Vendor', 'name_km' => 'អ្នកលក់'],
            ['id' => UserType::ADMIN_ID, 'name_en' => 'Admin', 'name_km' => 'អ្នកគ្រប់គ្រង'],
        ], ['id'], ['name_en', 'name_km']);
    }

    public function test_can_create_ai_chat_session(): void
    {
        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE_ID,
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
        $mockAiService = Mockery::mock(AiService::class);
        $mockAiService->shouldReceive('assertAvailable')->once();
        $this->app->instance(AiService::class, $mockAiService);

        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE_ID,
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

    public function test_store_message_returns_provider_error_before_queueing(): void
    {
        Queue::fake();
        $mockAiService = Mockery::mock(AiService::class);
        $mockAiService->shouldReceive('assertAvailable')
            ->once()
            ->andThrow(new \RuntimeException('Gemini API error (429): quota exceeded'));
        $mockAiService->shouldReceive('normalizeFailureMessage')
            ->once()
            ->andReturn('AI usage limit reached. Please try again later or switch provider.');
        $this->app->instance(AiService::class, $mockAiService);

        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE_ID,
            'user_type_id' => UserType::CONSUMER_ID,
        ]);

        $session = AiChatSession::query()->create([
            'user_id' => $user->id,
            'session_id' => 'ai-test-provider-unavailable',
            'title' => 'Provider unavailable',
            'last_message_at' => now(),
        ]);

        $token = $user->createToken('ai_message_provider_down')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ai/chat/messages', [
                'session_id' => $session->session_id,
                'message' => 'Hello',
            ]);

        $response->assertStatus(503)
            ->assertJsonPath('status.success', false)
            ->assertJsonPath('status.message', 'AI usage limit reached. Please try again later or switch provider.');

        $this->assertDatabaseMissing('ai_chat_messages', [
            'ai_chat_session_id' => $session->id,
            'content' => 'Hello',
        ]);

        Queue::assertNothingPushed();
    }
}
