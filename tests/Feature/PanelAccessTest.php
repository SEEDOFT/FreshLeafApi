<?php

namespace Tests\Feature;

use App\Models\AdminProfile;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
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

    public function test_guest_is_redirected_to_login_for_panel_routes(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/vendor')->assertRedirect(route('login'));
    }

    public function test_super_admin_can_access_admin_panel_and_is_blocked_from_vendor_panel(): void
    {
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        AdminProfile::query()->create([
            'user_id' => $admin->id,
            'super_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('panels.admin.panel_title'));

        $this->actingAs($admin)
            ->get('/vendor')
            ->assertForbidden();
    }

    public function test_vendor_can_access_vendor_panel_and_is_blocked_from_admin_panel(): void
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        $this->actingAs($vendor)
            ->get('/vendor')
            ->assertOk()
            ->assertSee(__('panels.vendor.panel_title'));

        $this->actingAs($vendor)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_without_super_admin_flag_is_forbidden_from_admin_panel(): void
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
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_authenticated_user_can_update_locale_and_theme_preferences(): void
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        $this->actingAs($vendor)
            ->post(route('preferences.locale'), ['locale' => 'en'])
            ->assertRedirect();

        $this->actingAs($vendor)
            ->post(route('preferences.theme'), ['theme' => 'dark'])
            ->assertRedirect();
    }

    public function test_pending_vendor_cannot_access_vendor_panel(): void
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::PENDING,
        ]);

        $this->actingAs($vendor)
            ->get('/vendor')
            ->assertForbidden();
    }
}
