<?php

namespace Tests\Feature;

use App\Models\AdminProfile;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminVendorReviewWebTest extends TestCase
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

    public function test_super_admin_can_open_pending_vendor_pages_and_approve_vendor(): void
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
            'business_name' => 'Pending Web Vendor',
            'contact_phone' => $vendor->phone_number,
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 123',
            'is_verified' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.web.vendors.pending'))
            ->assertOk()
            ->assertSee('Pending Web Vendor');

        $this->actingAs($admin)
            ->get(route('admin.web.vendors.pending.show', $vendor))
            ->assertOk()
            ->assertSee('Pending Web Vendor');

        $this->actingAs($admin)
            ->post(route('admin.web.vendors.pending.approve', $vendor))
            ->assertRedirect(route('admin.web.vendors.pending'));

        $this->assertDatabaseHas('users', [
            'id' => $vendor->id,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        $this->assertDatabaseHas('vendor_profiles', [
            'user_id' => $vendor->id,
            'is_verified' => true,
        ]);
    }

    public function test_non_super_admin_is_forbidden_from_pending_vendor_web_routes(): void
    {
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        AdminProfile::query()->create([
            'user_id' => $admin->id,
            'super_admin' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.web.vendors.pending'))
            ->assertForbidden();
    }

    public function test_super_admin_can_reject_pending_vendor(): void
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
            'business_name' => 'Reject Vendor',
            'is_verified' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.web.vendors.pending.reject', $vendor))
            ->assertRedirect(route('admin.web.vendors.pending'));

        $this->assertDatabaseHas('users', [
            'id' => $vendor->id,
            'user_status_id' => UserStatus::INACTIVE,
        ]);

        $this->assertDatabaseHas('vendor_profiles', [
            'user_id' => $vendor->id,
            'is_verified' => false,
        ]);
    }
}
