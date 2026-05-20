<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductStatus>
 */
class ProductStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1, 1000),
            'name_en' => $this->faker->word(),
            'name_km' => $this->faker->word().' (KM)',
        ];
    }
}
