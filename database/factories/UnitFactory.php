<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1, 1000),
            'name_en' => $this->faker->unique()->word(),
            'name_km' => $this->faker->unique()->word().' (KM)',
            'symbol' => $this->faker->unique()->lexify('??'),
            'conversion_to_base' => 1.0,
        ];
    }
}
