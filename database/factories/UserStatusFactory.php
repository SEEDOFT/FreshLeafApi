<?php

declare(strict_types=1);

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
        $name = fake()->unique()->word();
        return [
            'id' => fake()->unique()->numberBetween(1000, 999999),
            'code' => strtoupper($name),
            'name' => ucfirst($name),
        ];
    }
}
