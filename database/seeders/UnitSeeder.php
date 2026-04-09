<?php

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
            ['name' => 'Kilogram', 'symbol' => 'kg', 'conversion_to_base' => 1],
            ['name' => 'Gram', 'symbol' => 'g', 'conversion_to_base' => 0.001],
            ['name' => 'Quantity', 'symbol' => 'qty', 'conversion_to_base' => 1],
            ['name' => 'Bunch', 'symbol' => 'bunch', 'conversion_to_base' => 1],
            ['name' => 'Pack', 'symbol' => 'pack', 'conversion_to_base' => 1],
            ['name' => 'Tray', 'symbol' => 'tray', 'conversion_to_base' => 1],
            ['name' => 'Piece', 'symbol' => 'piece', 'conversion_to_base' => 1],
        ];

        foreach ($units as $unit) {
            Unit::query()->updateOrCreate(
                ['symbol' => $unit['symbol']],
                [
                    'name' => $unit['name'],
                    'conversion_to_base' => $unit['conversion_to_base'],
                ]
            );
        }
    }
}
