<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ApiErrorEnvelopeTest extends TestCase
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
            ['id' => UserType::CONSUMER, 'name' => 'Consumer'],
            ['id' => UserType::OPERATION, 'name' => 'Operation'],
            ['id' => UserType::ADMIN, 'name' => 'Admin'],
        ]);

        Route::middleware('api')->get('/api/v1/test/error-envelope-500', static function (): never {
            throw new RuntimeException('Synthetic failure');
        });

        Route::middleware('api')->get('/api/v1/test/error-envelope-403', static function (): never {
            abort(403, 'Forbidden test');
        });
    }

    public function test_unauthenticated_requests_return_standard_error_envelope(): void
    {
        $response = $this->postJson('/api/v1/ai/chat/sessions', []);

        $response->assertStatus(401)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data',
            ])
            ->assertJsonPath('status.code', '401')
            ->assertJsonPath('status.success', false);
    }

    public function test_forbidden_requests_return_standard_error_envelope(): void
    {
        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER,
        ]);

        $token = $user->createToken('forbidden_test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/test/error-envelope-403');

        $response->assertStatus(403)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data',
            ])
            ->assertJsonPath('status.code', '403')
            ->assertJsonPath('status.success', false);
    }

    public function test_validation_errors_return_standard_error_envelope(): void
    {
        $user = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER,
        ]);

        $token = $user->createToken('validation_test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ai/chat/messages', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => ['errors'],
            ])
            ->assertJsonPath('status.code', '422')
            ->assertJsonPath('status.success', false);
    }

    public function test_unhandled_errors_return_standard_error_envelope(): void
    {
        $response = $this->getJson('/api/v1/test/error-envelope-500');

        $response->assertStatus(500)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data',
            ])
            ->assertJsonPath('status.code', '500')
            ->assertJsonPath('status.success', false);
    }
}
