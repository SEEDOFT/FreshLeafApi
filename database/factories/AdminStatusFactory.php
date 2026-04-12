<?php

namespace Database\Factories;

use App\Models\AdminStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminStatus>
 */
class AdminStatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Active', 'Inactive', 'Pending', 'Suspended']),
        ];
    }
}
