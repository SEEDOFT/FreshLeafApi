<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiChatSession;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
            ['id' => UserType::USER, 'code' => 'USER', 'name' => 'User'],
            ['id' => UserType::VENDOR, 'code' => 'VENDOR', 'name' => 'Vendor'],
            ['id' => UserType::ADMIN, 'code' => 'ADMIN', 'name' => 'Admin'],
        ], ['id'], ['code', 'name']);
    }

    public function test_broadcast_auth_succeeds_for_session_owner(): void
    {
        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::USER,
        ]);

        $session = AiChatSession::query()->create([
            'user_id' => $user->id,
            'session_id' => 'session-auth-ok',
            'title' => 'Auth Test',
            'last_message_at' => now(),
        ]);

        $token = $user->createToken('broadcast_auth')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/broadcasting/auth', [
                'socket_id' => '999.999',
                'channel_name' => 'private-ai-chat.'.$user->id.'.'.$session->session_id,
            ]);

        $response->assertOk();
    }
}
