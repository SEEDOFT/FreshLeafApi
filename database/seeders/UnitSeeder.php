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
        $data = [
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
        ];

        foreach ($data as $d) {
            Unit::create($d);
        }
    }
}
