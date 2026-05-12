<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'id' => 1,
                'name_en' => 'Kilogram',
                'name_km' => 'គីឡូក្រាម',
                'symbol' => 'kg',
                'conversion_to_base' => 1.0,
            ],
            [
                'id' => 2,
                'name_en' => 'Gram',
                'name_km' => 'ក្រាម',
                'symbol' => 'g',
                'conversion_to_base' => 0.001,
            ],
            [
                'id' => 3,
                'name_en' => 'Piece',
                'name_km' => 'ដុំ',
                'symbol' => 'pcs',
                'conversion_to_base' => 1.0,
            ],
            [
                'id' => 4,
                'name_en' => 'Bundle',
                'name_km' => 'បាច់',
                'symbol' => 'bundle',
                'conversion_to_base' => 1.0,
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
