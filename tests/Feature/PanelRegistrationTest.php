<?php

namespace Tests\Feature;

use App\Models\Vendor;
use App\Models\VendorStatus;
use App\Models\VendorType;
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

        $vendor = Vendor::query()->where('email', 'vendor.api@example.test')->first();

        $this->assertNotNull($vendor);
        $this->assertSame(VendorType::STANDART, (int) $vendor->type_id);
        $this->assertSame(VendorStatus::PENDING, (int) $vendor->status_id);
        $this->assertFalse((bool) $vendor->is_verified);
    }

    public function test_pending_vendor_cannot_login_and_active_vendor_can_login(): void
    {
        Vendor::query()->create([
            'name' => 'Pending Vendor',
            'email' => 'pending-login@test.local',
            'password' => bcrypt('password123'),
            'type_id' => VendorType::STANDART,
            'status_id' => VendorStatus::PENDING,
            'business_name' => 'Pending Shop',
            'contact_phone' => '+85510000112',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 10',
            'is_verified' => false,
        ]);

        Vendor::query()->create([
            'name' => 'Active Vendor',
            'email' => 'active-login@test.local',
            'password' => bcrypt('password123'),
            'type_id' => VendorType::STANDART,
            'status_id' => VendorStatus::ACTIVE,
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
