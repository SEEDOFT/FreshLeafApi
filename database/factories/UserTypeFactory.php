<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UserType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserType>
 */
class UserTypeFactory extends Factory
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
