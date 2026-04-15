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

        UserStatus::insert([
            ['id' => UserStatus::ACTIVE, 'name' => 'Active'],
            ['id' => UserStatus::INACTIVE, 'name' => 'Inactive'],
            ['id' => UserStatus::DELETED, 'name' => 'Deleted'],
        ]);

        UserType::insert([
            ['id' => UserType::USER, 'name' => 'User'],
            ['id' => UserType::VENDOR, 'name' => 'Vendor'],
            ['id' => UserType::ADMIN, 'name' => 'Admin'],
        ]);
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
