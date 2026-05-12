<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiChatSession;
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
            ['id' => UserStatus::ACTIVE, 'code' => 'ACTIVE', 'name' => 'Active'],
            ['id' => UserStatus::INACTIVE, 'code' => 'INACTIVE', 'name' => 'Inactive'],
            ['id' => UserStatus::DELETED, 'code' => 'DELETED', 'name' => 'Deleted'],
        ], ['id'], ['code', 'name']);

        UserType::upsert([
            ['id' => UserType::CONSUMER_ID, 'code' => 'USER', 'name' => 'User'],
            ['id' => UserType::VENDOR, 'code' => 'VENDOR', 'name' => 'Vendor'],
            ['id' => UserType::ADMIN, 'code' => 'ADMIN', 'name' => 'Admin'],
        ], ['id'], ['code', 'name']);

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
            'user_status_id' => UserStatus::ACTIVE,
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

    public function test_support_admin_broadcast_auth_succeeds_for_admin(): void
    {
        $admin = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::ADMIN,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '999.999',
            'channel_name' => 'private-support.admin',
        ]);

        $response->assertOk();
    }

    public function test_support_admin_broadcast_auth_rejects_normal_user(): void
    {
        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER_ID,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '999.999',
            'channel_name' => 'private-support.admin',
        ]);

        $response->assertForbidden();
    }
}
