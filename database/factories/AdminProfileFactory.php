<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminProfile>
 */
class AdminProfileFactory extends Factory
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
            'department' => fake()->randomElement(['Operations', 'Platform']),
            'job_title' => fake()->jobTitle(),
            'office_phone' => fake()->phoneNumber(),
            'super_admin' => false,
            'permissions' => ['vendors.review'],
        ];
    }
}
