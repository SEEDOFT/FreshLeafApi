<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PanelRegistrationTest extends TestCase
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

    public function test_guest_can_view_register_page(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(__('panels.auth.register'));
    }

    public function test_vendor_registration_creates_pending_vendor_profile_and_redirects_to_login_with_notice(): void
    {
        $response = $this->post(route('register.store'), [
            'first_name' => 'Sok',
            'last_name' => 'Dara',
            'email' => 'vendor.panel@example.test',
            'phone_number' => '+85512999111',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'FreshLeaf Organic Vendor',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'address' => 'Street 271',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $user = User::query()->where('email', 'vendor.panel@example.test')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserType::VENDOR, (int) $user->user_type_id);
        $this->assertSame(UserStatus::PENDING, (int) $user->user_status_id);
        $this->assertNotNull($user->vendorProfile);
        $this->assertSame('FreshLeaf Organic Vendor', $user->vendorProfile?->business_name);
    }
}
