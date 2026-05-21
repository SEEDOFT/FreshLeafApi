<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Admin\Resources\Vendors\Pages\CreateVendor;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminVendorPhoneValidationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_vendor_phone_number_is_validated_when_live_state_updates(): void
    {
        $admin = User::query()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone_number' => '+85510000901',
            'email' => 'admin-vendor-phone@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin->adminProfile()->create(['super_admin' => true]);

        User::query()->create([
            'first_name' => 'Existing',
            'last_name' => 'Vendor',
            'phone_number' => '+855123456789',
            'email' => 'existing-vendor-phone@test.local',
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
