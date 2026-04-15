<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PanelRegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_vendor_registration_creates_pending_vendor_via_api(): void
    {
        $response = $this->postJson('/api/v1/vendor/auth/register', [
            'name' => 'Sok Dara',
            'email' => 'vendor.api@example.test',
            'contact_phone' => '+85512999111',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'FreshLeaf Organic Vendor',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 271',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.status', 'pending');

        $vendor = User::query()->where('email', 'vendor.api@example.test')->first();

        $this->assertNotNull($vendor);
        $this->assertSame(UserType::VENDOR, (int) $vendor->user_type_id);
        $this->assertSame(UserStatus::PENDING, (int) $vendor->user_status_id);
        $this->assertFalse((bool) $vendor->vendorProfile?->is_verified);
    }

    public function test_pending_vendor_cannot_login_and_active_vendor_can_login(): void
    {
        $pendingVendor = User::query()->create([
            'first_name' => 'Pending',
            'last_name' => 'Vendor',
            'phone_number' => '+85510000112',
            'email' => 'pending-login@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);
        $pendingVendor->vendorProfile()->create([
            'business_name' => 'Pending Shop',
            'contact_phone' => '+85510000112',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 10',
            'is_verified' => false,
        ]);

        $activeVendor = User::query()->create([
            'first_name' => 'Active',
            'last_name' => 'Vendor',
            'phone_number' => '+85510000113',
            'email' => 'active-login@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $activeVendor->vendorProfile()->create([
            'business_name' => 'Active Shop',
            'contact_phone' => '+85510000113',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 11',
            'is_verified' => true,
        ]);

        $this->postJson('/api/v1/vendor/auth/login', [
            'email' => 'pending-login@test.local',
            'password' => 'password123',
        ])
            ->assertForbidden()
            ->assertJsonPath('status.message', 'Your account is pending approval');

        $this->postJson('/api/v1/vendor/auth/login', [
            'email' => 'active-login@test.local',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.token_type', 'Bearer');
    }
}
