<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PanelPreference;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_login_logout_and_dashboard_access_work_via_api(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000091',
            'email' => 'admin-access@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin-access@test.local',
            'password' => 'password123',
        ]);

        $loginResponse->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.token_type', 'Bearer');

        $token = (string) $loginResponse->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.module', 'dashboard');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/admin/auth/logout')
            ->assertOk()
            ->assertJsonPath('status.message', 'Tokens Revoked');
    }

    public function test_admin_token_cannot_access_vendor_routes(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000092',
            'email' => 'admin-isolation@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        $vendor = User::query()->create([
            'first_name' => 'Active',
            'last_name' => 'Vendor',
            'phone_number' => '+85510000111',
            'email' => 'vendor-isolation@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $vendor->vendorProfile()->create([
            'business_name' => 'Isolation Vendor',
            'contact_phone' => '+85510000111',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 1',
            'is_verified' => true,
        ]);

        $adminToken = (string) $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin-isolation@test.local',
            'password' => 'password123',
        ])->json('data.access_token');

        $this->assertNotSame('', $adminToken);

        $adminTokenId = (int) explode('|', $adminToken)[0];
        $this->assertSame(User::class, PersonalAccessToken::query()->findOrFail($adminTokenId)->tokenable_type);

        $this->withToken($adminToken)
            ->getJson('/api/v1/vendor/dashboard')
            ->assertForbidden();
    }

    public function test_vendor_token_cannot_access_admin_routes(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000093',
            'email' => 'admin-isolation-two@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        $vendor = User::query()->create([
            'first_name' => 'Active',
            'last_name' => 'Vendor',
            'phone_number' => '+85510000121',
            'email' => 'vendor-isolation-two@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $vendor->vendorProfile()->create([
            'business_name' => 'Isolation Vendor',
            'contact_phone' => '+85510000121',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 2',
            'is_verified' => true,
        ]);

        $vendorToken = (string) $this->postJson('/api/v1/vendor/auth/login', [
            'email' => 'vendor-isolation-two@test.local',
            'password' => 'password123',
        ])->json('data.access_token');

        $this->assertNotSame('', $vendorToken);

        $vendorTokenId = (int) explode('|', $vendorToken)[0];
        $this->assertSame(User::class, PersonalAccessToken::query()->findOrFail($vendorTokenId)->tokenable_type);

        $this->withToken($vendorToken)
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_preferences_can_be_saved_and_loaded(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'Preferences',
            'phone_number' => '+85510000094',
            'email' => 'admin-preferences@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        $token = (string) $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin-preferences@test.local',
            'password' => 'password123',
        ])->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/preferences', [
                'locale' => 'en',
                'theme' => 'dark',
            ])
            ->assertOk()
            ->assertJsonPath('data.locale', 'en')
            ->assertJsonPath('data.theme', 'dark');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/preferences')
            ->assertOk()
            ->assertJsonPath('data.locale', 'en')
            ->assertJsonPath('data.theme', 'dark');

        $this->assertDatabaseCount('panel_preferences', 1);
        $this->assertNotNull(PanelPreference::query()->first());
    }
}
