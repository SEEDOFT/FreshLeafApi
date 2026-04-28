<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name_en' => $name,
            'name_km' => $name.' (Khmer)',
            'description_en' => $this->faker->sentence(),
            'description_km' => $this->faker->sentence().' (Khmer)',
            'slug' => Str::slug($name),
            'is_active' => true,
        ];
    }
}
