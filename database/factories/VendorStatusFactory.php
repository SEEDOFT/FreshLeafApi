<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VendorStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorStatus>
 */
class VendorStatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Active', 'Inactive', 'Pending', 'Suspended', 'Rejected']),
        ];
    }
}
