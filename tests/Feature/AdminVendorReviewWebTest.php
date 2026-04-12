<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminStatus;
use App\Models\AdminType;
use App\Models\Vendor;
use App\Models\VendorStatus;
use App\Models\VendorType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminVendorReviewWebTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_review_and_approve_pending_vendor_via_api(): void
    {
        Admin::query()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin-review@test.local',
            'password' => bcrypt('password123'),
            'type_id' => AdminType::SUPER_ADMIN,
            'status_id' => AdminStatus::ACTIVE,
            'super_admin' => true,
        ]);

        $vendor = Vendor::query()->create([
            'name' => 'Pending Vendor',
            'email' => 'pending-review@test.local',
            'password' => bcrypt('password123'),
            'type_id' => VendorType::STANDART,
            'status_id' => VendorStatus::PENDING,
            'business_name' => 'Pending Review Store',
            'contact_phone' => '+85510000114',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 12',
            'is_verified' => false,
        ]);

        $token = (string) $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'superadmin-review@test.local',
            'password' => 'password123',
        ])->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/vendors/pending')
            ->assertOk()
            ->assertJsonPath('status.success', true);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/vendors/pending/'.$vendor->id)
            ->assertOk()
            ->assertJsonPath('data.id', $vendor->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/vendors/'.$vendor->id, [
                'action' => 'approve',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'status_id' => VendorStatus::ACTIVE,
            'is_verified' => true,
        ]);
    }

    public function test_non_super_admin_is_forbidden_from_pending_vendor_review_endpoints(): void
    {
        Admin::query()->create([
            'name' => 'Operation Admin',
            'email' => 'operation-review@test.local',
            'password' => bcrypt('password123'),
            'type_id' => AdminType::OPERATION,
            'status_id' => AdminStatus::ACTIVE,
            'super_admin' => false,
        ]);

        $vendor = Vendor::query()->create([
            'name' => 'Pending Vendor',
            'email' => 'pending-review-deny@test.local',
            'password' => bcrypt('password123'),
            'type_id' => VendorType::STANDART,
            'status_id' => VendorStatus::PENDING,
            'business_name' => 'Denied Store',
            'contact_phone' => '+85510000115',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 13',
            'is_verified' => false,
        ]);

        $token = (string) $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'operation-review@test.local',
            'password' => 'password123',
        ])->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/vendors/pending')
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/vendors/'.$vendor->id, [
                'action' => 'reject',
            ])
            ->assertForbidden();
    }
}
