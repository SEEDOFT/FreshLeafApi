<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceHistory>
 */
class PriceHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $oldPrice = $this->faker->randomFloat(2, 1, 500);

        return [
            'product_id' => Product::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'old_price' => $oldPrice,
            'new_price' => $oldPrice * 1.1,
            'changed_by' => User::factory(),
            'changed_at' => now(),
        ];
    }
}
