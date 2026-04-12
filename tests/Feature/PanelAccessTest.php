<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminStatus;
use App\Models\AdminType;
use App\Models\PanelPreference;
use App\Models\Vendor;
use App\Models\VendorStatus;
use App\Models\VendorType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_login_logout_and_dashboard_access_work_via_api(): void
    {
        Admin::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin-access@test.local',
            'password' => bcrypt('password123'),
            'type_id' => AdminType::SUPER_ADMIN,
            'status_id' => AdminStatus::ACTIVE,
            'super_admin' => true,
        ]);

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
        Admin::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin-isolation@test.local',
            'password' => bcrypt('password123'),
            'type_id' => AdminType::SUPER_ADMIN,
            'status_id' => AdminStatus::ACTIVE,
            'super_admin' => true,
        ]);

        Vendor::query()->create([
            'name' => 'Active Vendor',
            'email' => 'vendor-isolation@test.local',
            'password' => bcrypt('password123'),
            'type_id' => VendorType::STANDART,
            'status_id' => VendorStatus::ACTIVE,
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
        $this->assertSame(Admin::class, PersonalAccessToken::query()->findOrFail($adminTokenId)->tokenable_type);

        $this->withToken($adminToken)
            ->getJson('/api/v1/vendor/dashboard')
            ->assertForbidden();
    }

    public function test_vendor_token_cannot_access_admin_routes(): void
    {
        Admin::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin-isolation-two@test.local',
            'password' => bcrypt('password123'),
            'type_id' => AdminType::SUPER_ADMIN,
            'status_id' => AdminStatus::ACTIVE,
            'super_admin' => true,
        ]);

        Vendor::query()->create([
            'name' => 'Active Vendor',
            'email' => 'vendor-isolation-two@test.local',
            'password' => bcrypt('password123'),
            'type_id' => VendorType::STANDART,
            'status_id' => VendorStatus::ACTIVE,
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
        $this->assertSame(Vendor::class, PersonalAccessToken::query()->findOrFail($vendorTokenId)->tokenable_type);

        $this->withToken($vendorToken)
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_preferences_can_be_saved_and_loaded(): void
    {
        Admin::query()->create([
            'name' => 'Admin Preferences',
            'email' => 'admin-preferences@test.local',
            'password' => bcrypt('password123'),
            'type_id' => AdminType::SUPER_ADMIN,
            'status_id' => AdminStatus::ACTIVE,
            'super_admin' => true,
        ]);

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
