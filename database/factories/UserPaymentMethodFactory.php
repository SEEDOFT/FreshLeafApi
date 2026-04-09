<?php

namespace Database\Factories;

use App\Models\UserPaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPaymentMethod>
 */
class UserPaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->randomNumber(),
            'payment_method_type_id' => 1,
            'payment_method_status_id' => 1,
            'label' => fake()->word(),
            'card_holder_name' => fake()->name(),
            'card_number' => fake()->creditCardNumber(),
            'expiry_month' => fake()->numberBetween(1, 12),
            'expiry_year' => fake()->numberBetween(2025, 2030),
            'cvv' => '123',
            'is_default' => false,
        ];
    }
}
