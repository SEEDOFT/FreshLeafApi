<?php

namespace Tests\Feature;

use App\Models\AdminProfile;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class VendorApprovalTest extends TestCase
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

    public function test_super_admin_can_list_and_approve_pending_vendors(): void
    {
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        AdminProfile::query()->create([
            'user_id' => $admin->id,
            'super_admin' => true,
        ]);

        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);

        VendorProfile::query()->create([
            'user_id' => $vendor->id,
            'business_name' => 'Pending Vendor',
            'contact_phone' => $vendor->phone_number,
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 200',
            'is_verified' => false,
        ]);

        $token = $admin->createToken('super-admin')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/vendors/pending')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.0.id', $vendor->id);

        $this->withToken($token)
            ->postJson('/api/v1/admin/vendors/'.$vendor->id.'/approve')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.vendor_id', $vendor->id)
            ->assertJsonPath('data.user_status_id', UserStatus::ACTIVE)
            ->assertJsonPath('data.is_verified', true);

        $this->assertDatabaseHas('users', [
            'id' => $vendor->id,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        $this->assertDatabaseHas('vendor_profiles', [
            'user_id' => $vendor->id,
            'is_verified' => true,
        ]);
    }

    public function test_non_super_admin_cannot_approve_pending_vendor(): void
    {
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        AdminProfile::query()->create([
            'user_id' => $admin->id,
            'super_admin' => false,
        ]);

        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);

        VendorProfile::query()->create([
            'user_id' => $vendor->id,
            'business_name' => 'Pending Vendor',
        ]);

        $token = $admin->createToken('admin')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/admin/vendors/'.$vendor->id.'/approve')
            ->assertStatus(403)
            ->assertJsonPath('status.success', false);
    }
}
