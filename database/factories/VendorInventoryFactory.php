<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\VendorInventory;
use App\Models\VendorInventoryStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorInventory>
 */
class VendorInventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => User::factory(),
            'product_id' => Product::factory(),
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'currency_id' => Currency::USD_ID,
            'price' => $this->faker->randomFloat(2, 0.5, 50),
            'stock_quantity' => $this->faker->randomFloat(2, 0, 1000),
            'unit_id' => Unit::factory(),
            'harvest_date' => $this->faker->date(),
            'farm_location' => $this->faker->city(),
            'province_of_origin' => $this->faker->state(),
            'certification_type' => $this->faker->randomElement(['Organic', 'Pesticide-Free', 'Naturally Grown']),
            'shelf_life_days' => $this->faker->numberBetween(1, 30),
        ];
    }
}
