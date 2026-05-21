<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Vendor\Pages\Auth\Register;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorRegistrationPhoneValidationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_vendor_registration_phone_number_is_validated_when_live_state_updates(): void
    {
        User::query()->create([
            'first_name' => 'Existing',
            'last_name' => 'Vendor',
            'phone_number' => '+855123456789',
            'email' => 'existing-register-phone@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        Livewire::test(Register::class)
            ->set('data.phone_number', '012 345 6789')
            ->assertHasErrors(['data.phone_number']);
    }
}
