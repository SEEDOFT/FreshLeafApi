<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InventoryMovementType;
use Illuminate\Database\Seeder;

class InventoryMovementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'RESTOCK', 'name_en' => 'Restock', 'name_km' => 'បញ្ចូលស្តុក'],
            ['id' => 2, 'code' => 'SALE', 'name_en' => 'Sale', 'name_km' => 'លក់ចេញ'],
            ['id' => 3, 'code' => 'ADJUSTMENT', 'name_en' => 'Adjustment', 'name_km' => 'កែតម្រូវ'],
            ['id' => 4, 'code' => 'DAMAGE', 'name_en' => 'Damage', 'name_km' => 'ខូចខាត'],
        ];

        foreach ($data as $d) {
            InventoryMovementType::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
