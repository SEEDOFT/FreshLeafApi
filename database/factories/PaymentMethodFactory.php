<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'payment_type_id' => PaymentType::factory(),
            'payment_status_id' => PaymentStatus::factory(),
            'label' => $this->faker->words(2, true),
            'card_holder_name' => $this->faker->name(),
            'card_number' => $this->faker->creditCardNumber(),
            'expiry_month' => $this->faker->numberBetween(1, 12),
            'expiry_year' => (int) $this->faker->year('+10 years'),
            'cvv' => (string) $this->faker->numberBetween(100, 999),
            'is_default' => false,
        ];
    }
}
