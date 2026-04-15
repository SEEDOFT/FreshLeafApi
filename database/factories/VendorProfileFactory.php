<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorProfile>
 */
class VendorProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
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
