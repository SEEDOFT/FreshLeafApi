<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaymentMethodType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethodType>
 */
class PaymentMethodTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1000, 999999),
            'code' => $this->faker->unique()->slug(),
            'name' => $this->faker->words(2, true),
        ];
    }
}
