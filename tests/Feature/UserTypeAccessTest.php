<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserTypeAccessTest extends TestCase
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

    public function test_vendor_route_rejects_consumer_user(): void
    {
        $consumer = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::CONSUMER,
        ]);

        $token = $consumer->createToken('consumer-token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/vendor/me')
            ->assertStatus(403)
            ->assertJsonPath('status.success', false);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/vendor/profile')
            ->assertStatus(403)
            ->assertJsonPath('status.success', false);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/users/consumer-profile')
            ->assertOk()
            ->assertJsonPath('status.success', true);
    }

    public function test_vendor_route_allows_active_vendor_user(): void
    {
        $vendor = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::VENDOR,
        ]);

        $token = $vendor->createToken('vendor-token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/vendor/me')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.id', $vendor->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/vendor/overview')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => [
                    'suppliers_total',
                    'product_categories_total',
                    'products_total',
                    'product_variants_total',
                ],
            ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/vendor/profile')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => [
                    'business_name',
                    'contact_phone',
                    'city',
                    'province',
                    'address',
                    'is_verified',
                    'meta',
                    'updated_at',
                ],
            ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/vendor/profile', [
                'business_name' => 'Kandal Green Operations Co., Ltd.',
                'contact_phone' => '+85512999888',
                'city' => 'Ta Khmau',
                'province' => 'Kandal',
                'address' => 'National Road 21A, Kandal',
                'meta' => ['warehouse' => 'kandal-central'],
            ])
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.business_name', 'Kandal Green Operations Co., Ltd.')
            ->assertJsonPath('data.city', 'Ta Khmau')
            ->assertJsonPath('data.province', 'Kandal');
    }

    public function test_admin_route_allows_active_admin_user(): void
    {
        $admin = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::ADMIN,
        ]);

        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        $this->withToken($adminToken)
            ->getJson('/api/v1/admin/me')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.id', $admin->id);

        $this->withToken($adminToken)
            ->getJson('/api/v1/admin/overview')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => [
                    'users' => ['total', 'active', 'consumers', 'vendors', 'admins'],
                    'catalog' => ['product_categories_total', 'products_total', 'product_variants_total', 'suppliers_total'],
                ],
            ]);

        $this->withToken($adminToken)
            ->getJson('/api/v1/admin/profile')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => ['department', 'job_title', 'office_phone', 'super_admin', 'permissions', 'updated_at'],
            ]);

        $this->withToken($adminToken)
            ->putJson('/api/v1/admin/profile', [
                'department' => 'Platform Engineering',
                'job_title' => 'Head of Operations',
                'office_phone' => '+85523999888',
                'super_admin' => true,
                'permissions' => ['users.manage', 'catalog.manage'],
            ])
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.department', 'Platform Engineering')
            ->assertJsonPath('data.super_admin', true);
    }

    public function test_admin_route_rejects_inactive_admin_user(): void
    {
        $inactiveAdmin = User::factory()->create([
            'user_status_id' => UserStatus::INACTIVE,
            'user_type_id' => UserType::ADMIN,
        ]);

        $inactiveToken = $inactiveAdmin->createToken('inactive-admin-token')->plainTextToken;

        $this->withToken($inactiveToken)
            ->getJson('/api/v1/admin/me')
            ->assertStatus(403)
            ->assertJsonPath('status.success', false);
    }

    public function test_admin_route_rejects_non_admin_user(): void
    {
        $vendor = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::VENDOR,
        ]);

        $vendorToken = $vendor->createToken('vendor-token')->plainTextToken;

        $this->withToken($vendorToken)
            ->getJson('/api/v1/admin/me')
            ->assertStatus(403)
            ->assertJsonPath('status.success', false);

        $this->withToken($vendorToken)
            ->getJson('/api/v1/admin/profile')
            ->assertStatus(403)
            ->assertJsonPath('status.success', false);
    }

    public function test_vendor_and_admin_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/vendor/me')
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);

        $this->getJson('/api/v1/vendor/overview')
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);

        $this->getJson('/api/v1/vendor/profile')
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);

        $this->putJson('/api/v1/vendor/profile', [
            'business_name' => 'Unauthorized Update',
        ])
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);

        $this->getJson('/api/v1/admin/me')
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);

        $this->getJson('/api/v1/admin/overview')
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);

        $this->getJson('/api/v1/admin/profile')
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);

        $this->putJson('/api/v1/admin/profile', [
            'department' => 'Unauthorized Update',
        ])
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);

        $this->getJson('/api/v1/users/consumer-profile')
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);

        $this->putJson('/api/v1/users/consumer-profile', [
            'preferred_language' => 'km',
        ])
            ->assertStatus(401)
            ->assertJsonPath('status.success', false);
    }
}
