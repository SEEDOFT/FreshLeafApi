<?php

namespace Database\Factories;

use App\Models\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserStatus>
 */
class UserStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1000, 999999),
            'name' => fake()->unique()->word(),
        ];
    }
}
