<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AdminType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminType>
 */
class AdminTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Super Admin', 'Operation', 'Support']),
        ];
    }
}
