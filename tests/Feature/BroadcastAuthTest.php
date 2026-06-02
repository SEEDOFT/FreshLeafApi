<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiChatSession;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\ConversationStatus;
use App\Models\ConversationType;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BroadcastAuthTest extends TestCase
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

        ConversationType::upsert([
            ['id' => ConversationType::DIRECT_ID, 'name' => 'direct'],
            ['id' => ConversationType::SUPPORT_ID, 'name' => 'support'],
        ], ['id'], ['name']);

        ConversationStatus::upsert([
            ['id' => ConversationStatus::OPEN_ID, 'name' => 'open'],
            ['id' => ConversationStatus::CLOSED_ID, 'name' => 'closed'],
        ], ['id'], ['name']);

        config()->set('broadcasting.default', 'reverb');
        config()->set('broadcasting.connections.reverb.key', 'test-key');
        config()->set('broadcasting.connections.reverb.secret', 'test-secret');
        config()->set('broadcasting.connections.reverb.app_id', 'test-app');
        Broadcast::forgetDrivers();

        require base_path('routes/channels.php');
    }

    public function test_broadcast_auth_succeeds_for_session_owner(): void
    {
        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE_ID,
            'user_type_id' => UserType::CONSUMER_ID,
        ]);

        $session = AiChatSession::query()->create([
            'user_id' => $user->id,
            'session_id' => 'session-auth-ok',
            'title' => 'Auth Test',
            'last_message_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '999.999',
            'channel_name' => 'private-ai-chat.'.$user->id.'.'.$session->session_id,
        ]);

        $response->assertOk();
    }

    public function test_chat_conversation_broadcast_auth_succeeds_for_participant(): void
    {
        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE_ID,
            'user_type_id' => UserType::VENDOR_ID,
        ]);
        $conversation = $this->createConversation($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '999.999',
            'channel_name' => 'private-chat.conversation.'.$conversation->id,
        ]);

        $response->assertOk();
    }

    public function test_chat_conversation_broadcast_auth_rejects_non_participant(): void
    {
        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE_ID,
            'user_type_id' => UserType::CONSUMER_ID,
        ]);
        $participant = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE_ID,
            'user_type_id' => UserType::VENDOR_ID,
        ]);
        $conversation = $this->createConversation($participant);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '999.999',
            'channel_name' => 'private-chat.conversation.'.$conversation->id,
        ]);

        $response->assertForbidden();
    }

    private function createConversation(User $participant): Conversation
    {
        $conversation = Conversation::query()->create([
            'conversation_type_id' => ConversationType::SUPPORT_ID,
            'conversation_status_id' => ConversationStatus::OPEN_ID,
        ]);

        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $participant->id,
        ]);

        return $conversation;
    }
}
