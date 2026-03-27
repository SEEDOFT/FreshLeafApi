<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration.
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'phone_number',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '1234567890',
        ]);
    }

    /**
     * Test user login.
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'phone_number' => '1234567890',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone_number' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => [
                    'access_token',
                    'token_type',
                    'user',
                ],
            ]);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'phone_number' => '1234567890',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone_number' => '1234567890',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => [
                    'code' => '401',
                    'success' => false,
                    'message' => 'Invalid login details',
                ],
                'data' => [],
            ]);
    }

    /**
     * Test user logout.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => [
                    'code' => '200',
                    'success' => true,
                    'message' => 'Tokens Revoked',
                ],
                'data' => [],
            ]);

        $this->assertCount(0, $user->tokens);
    }
}
