<?php

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

        UserStatus::insert([
            ['id' => UserStatus::ACTIVE, 'name' => 'Active'],
            ['id' => UserStatus::INACTIVE, 'name' => 'Inactive'],
            ['id' => UserStatus::DELETED, 'name' => 'Deleted'],
            ['id' => UserStatus::PENDING, 'name' => 'Pending'],
        ]);

        UserType::insert([
            ['id' => UserType::CONSUMER, 'name' => 'Consumer'],
            ['id' => UserType::OPERATION, 'name' => 'Operation'],
            ['id' => UserType::ADMIN, 'name' => 'Admin'],
        ]);
    }

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
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone_number' => '1234567890',
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
            'phone_number' => '1234567890',
            'password' => bcrypt('password123'),
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER,
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

    public function test_vendor_registration_is_pending_until_super_admin_approval(): void
    {
        $registerResponse = $this->postJson('/api/v1/vendor/auth/register', [
            'first_name' => 'Vendor',
            'last_name' => 'User',
            'email' => 'vendor.pending@example.com',
            'phone_number' => '85510000111',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'FreshLeaf Vendor Co',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 271',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Vendor registration submitted. Waiting for super admin approval.');

        $this->assertDatabaseHas('users', [
            'phone_number' => '85510000111',
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);

        $loginResponse = $this->postJson('/api/v1/vendor/auth/login', [
            'phone_number' => '85510000111',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(401)
            ->assertJsonPath('status.success', false)
            ->assertJsonPath('status.message', 'Invalid login details');
    }

    public function test_admin_registration_endpoint_is_not_available(): void
    {
        $this->postJson('/api/v1/admin/auth/register', [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone_number' => '85510000112',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();
    }
}
