<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\ProductType;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'product_category_id' => ProductCategory::factory(),
            'product_type_id' => ProductType::factory(),
            'default_unit_id' => Unit::factory(),
            'product_status_id' => ProductStatus::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'nutrition_data' => [
                'calories' => $this->faker->numberBetween(10, 500),
                'fat' => $this->faker->randomFloat(1, 0, 50),
            ],
            'shelf_life_days' => $this->faker->numberBetween(1, 365),
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_status_id' => ProductStatus::ACTIVE,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_status_id' => ProductStatus::INACTIVE,
        ]);
    }
}
