<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductSubstitution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductSubstitution>
 */
class ProductSubstitutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'substitute_product_id' => Product::factory(),
            'priority' => $this->faker->numberBetween(1, 10),
            'reason' => $this->faker->sentence(),
        ];
    }
}
