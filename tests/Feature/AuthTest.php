<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserStatus::upsert([
            ['id' => UserStatus::ACTIVE, 'code' => 'ACTIVE', 'name' => 'Active'],
            ['id' => UserStatus::INACTIVE, 'code' => 'INACTIVE', 'name' => 'Inactive'],
            ['id' => UserStatus::DELETED, 'code' => 'DELETED', 'name' => 'Deleted'],
            ['id' => UserStatus::PENDING, 'code' => 'PENDING', 'name' => 'Pending'],
        ], ['id'], ['code', 'name']);

        UserType::upsert([
            ['id' => UserType::USER, 'code' => 'USER', 'name' => 'User'],
            ['id' => UserType::VENDOR, 'code' => 'VENDOR', 'name' => 'Vendor'],
            ['id' => UserType::ADMIN, 'code' => 'ADMIN', 'name' => 'Admin'],
        ], ['id'], ['code', 'name']);
    }

    /**
     * Test user registration.
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '+855123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => [
                    'code' => '201',
                    'success' => true,
                    'message' => 'User registered successfully',
                ],
            ])
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => [
                    'access_token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '+855123456789',
        ]);
    }

    /**
     * Test user login.
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+855123456789',
            'password' => bcrypt('password123'),
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::USER,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone_number' => '+855123456789',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => [
                    'code' => '200',
                    'success' => true,
                    'message' => 'Login success',
                ],
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'access_token',
                    'token_type',
                ],
            ]);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'phone_number' => '+855123456789',
            'password' => bcrypt('password123'),
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::USER,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone_number' => '+855123456789',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => [
                    'code' => '401',
                    'success' => false,
                    'message' => 'Invalid login details',
                ],
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
            ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_admin_registration_requires_bootstrap_key(): void
    {
        config()->set('auth.admin_registration_key', 'dev-secret');

        $this->postJson('/api/v1/admin/auth/register', [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone_number' => '+85510000112',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertForbidden();
    }

    public function test_admin_can_register_with_bootstrap_key(): void
    {
        config()->set('auth.admin_registration_key', 'dev-secret');

        User::factory()->create([
            'first_name' => 'Normal',
            'last_name' => 'User',
            'phone_number' => '+85510000112',
            'user_type_id' => UserType::USER,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        $response = $this
            ->withHeader('X-Admin-Registration-Key', 'dev-secret')
            ->postJson('/api/v1/admin/auth/register', [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'phone_number' => '+85510000112',
                'email' => 'admin-register@test.local',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'super_admin' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Admin registered successfully')
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => ['admin_id', 'access_token', 'token_type'],
            ]);

        $this->assertDatabaseHas('users', [
            'phone_number' => '+85510000112',
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        $this->assertDatabaseHas('users', [
            'phone_number' => '+85510000112',
            'user_type_id' => UserType::USER,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
    }
}
