<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Admin\Resources\Vendors\Pages\CreateVendor;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Panel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserStatus::upsert([
            ['id' => UserStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
            ['id' => UserStatus::INACTIVE_ID, 'name_en' => 'Inactive', 'name_km' => 'អសកម្ម'],
            ['id' => UserStatus::DELETED_ID, 'name_en' => 'Deleted', 'name_km' => 'បានលុប'],
            ['id' => UserStatus::PENDING_ID, 'name_en' => 'Pending', 'name_km' => 'រង់ចាំ'],
        ], ['id'], ['name_en', 'name_km']);

        UserType::upsert([
            ['id' => UserType::CONSUMER_ID, 'name_en' => 'User', 'name_km' => 'អ្នកប្រើប្រាស់'],
            ['id' => UserType::VENDOR_ID, 'name_en' => 'Vendor', 'name_km' => 'អ្នកលក់'],
            ['id' => UserType::ADMIN_ID, 'name_en' => 'Admin', 'name_km' => 'អ្នកគ្រប់គ្រង'],
        ], ['id'], ['name_en', 'name_km']);

        Currency::upsert([
            ['id' => Currency::KHR_ID, 'code' => 'KHR', 'name_en' => 'Cambodian Riel', 'name_km' => 'រៀល', 'symbol' => '៛'],
            ['id' => Currency::USD_ID, 'code' => 'USD', 'name_en' => 'US Dollar', 'name_km' => 'ដុល្លារអាមេរិក', 'symbol' => '$'],
        ], ['id'], ['name_en', 'name_km', 'code', 'symbol']);

        ExchangeRate::create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4000.00000000',
        ]);
        ExchangeRate::create([
            'from_currency_id' => Currency::KHR_ID,
            'to_currency_id' => Currency::USD_ID,
            'rate' => '0.00025000',
        ]);
    }

    public function test_admin_user_can_access_admin_panel(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000101',
            'email' => 'admin-dashboard@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->assertSuccessful()
            ->assertSeeHtml('admin');
    }

    public function test_admin_user_can_view_user_resource_list(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000102',
            'email' => 'admin-userlist@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        $consumers = User::factory()->count(3)->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($consumers);
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $consumer = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $this->assertFalse(
            $consumer->canAccessPanel(app(Panel::class)->id('admin'))
        );
    }

    public function test_admin_vendor_creation_validates_phone_numbers(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000103',
            'email' => 'admin-vendor-validate@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        User::query()->create([
            'first_name' => 'Existing',
            'last_name' => 'Vendor',
            'phone_number' => '+855123456789',
            'email' => 'existing-vendor-validate@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateVendor::class)
            ->set('data.phone_number', '012 345 6789')
            ->assertHasErrors(['data.phone_number']);
    }
}
