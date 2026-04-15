<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VendorType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorType>
 */
class VendorTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Standart', 'Premium', 'Enterprise']),
        ];
    }
}
