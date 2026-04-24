<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserDeviceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_fcm_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/user/devices', [
            'device_token' => 'test-token-123',
            'device_type' => 'android',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.device_token', 'test-token-123');

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'device_token' => 'test-token-123',
            'device_type' => 'android',
            'is_active' => true,
        ]);
    }

    public function test_registering_existing_token_updates_it(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Existing device for other user
        UserDevice::factory()->create([
            'user_id' => $otherUser->id,
            'device_token' => 'test-token-123',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/user/devices', [
            'device_token' => 'test-token-123',
            'device_type' => 'ios',
        ]);

        $response->assertStatus(200);

        // Token should now belong to $user and be 'ios'
        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'device_token' => 'test-token-123',
            'device_type' => 'ios',
        ]);

        $this->assertDatabaseCount('user_devices', 1);
    }

    public function test_user_can_deactivate_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $device = UserDevice::factory()->create([
            'user_id' => $user->id,
            'device_token' => 'test-token-123',
        ]);

        $response = $this->deleteJson("/api/v1/user/devices/{$device->device_token}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_devices', [
            'device_token' => 'test-token-123',
            'is_active' => false,
        ]);
    }
}
