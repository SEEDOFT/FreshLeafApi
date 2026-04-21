<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminVendorReviewWebTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_review_and_approve_pending_vendor_via_api(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000095',
            'email' => 'superadmin-review@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        $vendor = User::query()->create([
            'first_name' => 'Pending',
            'last_name' => 'Vendor',
            'phone_number' => '+85510000114',
            'email' => 'pending-review@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);
        $vendor->vendorProfile()->create([
            'business_name' => 'Pending Review Store',
            'contact_phone' => '+85510000114',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 12',
            'is_verified' => false,
        ]);

        $token = (string) $this->postJson('/api/v1/admin/auth/login', [
            'phone_number' => '+85510000095',
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
            ->patchJson('/api/v1/admin/vendors/pending/'.$vendor->id, [
                'action' => 'approve',
                'reason' => 'All vendor documents are valid.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status.code', 'ACTIVE')
            ->assertJsonPath('data.approve_reason', 'All vendor documents are valid.')
            ->assertJsonPath('data.approved_by_admin_id', $admin->id);

        $this->assertDatabaseHas('users', [
            'id' => $vendor->id,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $this->assertDatabaseHas('vendor_profiles', [
            'user_id' => $vendor->id,
            'is_verified' => true,
            'approved_by_admin_id' => $admin->id,
            'approve_reason' => 'All vendor documents are valid.',
            'rejected_by_admin_id' => null,
            'reject_reason' => null,
        ]);

        $this->assertNotNull($vendor->vendorProfile()->value('approved_at'));
        $this->assertNull($vendor->vendorProfile()->value('rejected_at'));
    }

    public function test_super_admin_can_review_and_reject_pending_vendor_via_api(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000097',
            'email' => 'superadmin-reject@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        $vendor = User::query()->create([
            'first_name' => 'Pending',
            'last_name' => 'Vendor',
            'phone_number' => '+85510000117',
            'email' => 'pending-reject@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);
        $vendor->vendorProfile()->create([
            'business_name' => 'Reject Store',
            'contact_phone' => '+85510000117',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 14',
            'is_verified' => false,
        ]);

        $token = (string) $this->postJson('/api/v1/admin/auth/login', [
            'phone_number' => '+85510000097',
            'password' => 'password123',
        ])->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/vendors/pending/'.$vendor->id, [
                'action' => 'reject',
                'reason' => 'Business document mismatch.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status.code', 'INACTIVE')
            ->assertJsonPath('data.reject_reason', 'Business document mismatch.')
            ->assertJsonPath('data.rejected_by_admin_id', $admin->id);

        $this->assertDatabaseHas('users', [
            'id' => $vendor->id,
            'user_status_id' => UserStatus::INACTIVE,
        ]);
        $this->assertDatabaseHas('vendor_profiles', [
            'user_id' => $vendor->id,
            'is_verified' => false,
            'approved_by_admin_id' => null,
            'approve_reason' => null,
            'rejected_by_admin_id' => $admin->id,
            'reject_reason' => 'Business document mismatch.',
        ]);

        $this->assertNull($vendor->vendorProfile()->value('approved_at'));
        $this->assertNotNull($vendor->vendorProfile()->value('rejected_at'));
    }

    public function test_super_admin_reject_requires_reason(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000098',
            'email' => 'superadmin-reason@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        $vendor = User::query()->create([
            'first_name' => 'Pending',
            'last_name' => 'Vendor',
            'phone_number' => '+85510000118',
            'email' => 'pending-reason@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);
        $vendor->vendorProfile()->create([
            'business_name' => 'Reason Store',
            'contact_phone' => '+85510000118',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 15',
            'is_verified' => false,
        ]);

        $token = (string) $this->postJson('/api/v1/admin/auth/login', [
            'phone_number' => '+85510000098',
            'password' => 'password123',
        ])->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/vendors/pending/'.$vendor->id, [
                'action' => 'reject',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('status.code', '422')
            ->assertJsonPath('status.success', false);
    }

    public function test_non_super_admin_is_forbidden_from_pending_vendor_review_endpoints(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Operation',
            'last_name' => 'Admin',
            'phone_number' => '+85510000096',
            'email' => 'operation-review@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $admin->adminProfile()->create(['super_admin' => false]);

        $vendor = User::query()->create([
            'first_name' => 'Pending',
            'last_name' => 'Vendor',
            'phone_number' => '+85510000115',
            'email' => 'pending-review-deny@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);
        $vendor->vendorProfile()->create([
            'business_name' => 'Denied Store',
            'contact_phone' => '+85510000115',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 13',
            'is_verified' => false,
        ]);

        $token = (string) $this->postJson('/api/v1/admin/auth/login', [
            'phone_number' => '+85510000096',
            'password' => 'password123',
        ])->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/vendors/pending')
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/vendors/pending/'.$vendor->id, [
                'action' => 'reject',
                'reason' => 'Not allowed for non super admin.',
            ])
            ->assertForbidden();
    }
}
