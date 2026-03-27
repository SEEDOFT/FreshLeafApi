<?php

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
        return [
            'code' => $code = fake()->unique()->word(),
            'name' => ucfirst($code),
        ];
    }
}
