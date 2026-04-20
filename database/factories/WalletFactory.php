<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $usdId = (int) Currency::query()->where('code', Currency::USD)->value('id');

        return [
            'balance' => fake()->randomFloat(2, 0, 1000),
            'currency_id' => $usdId > 0 ? $usdId : null,
            'is_default' => false,
        ];
    }
}
