<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\VendorStatus;
use App\Models\VendorType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'email' => Str::lower(Str::slug($name)).'@vendor.local',
            'password' => Hash::make('password'),
            'type_id' => VendorType::STANDART,
            'status_id' => VendorStatus::PENDING,
            'business_name' => fake()->company(),
            'contact_phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'address' => fake()->streetAddress(),
            'is_verified' => false,
            'meta' => ['source' => 'factory'],
        ];
    }
}
