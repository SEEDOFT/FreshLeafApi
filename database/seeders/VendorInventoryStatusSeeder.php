<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\VendorInventoryStatus;
use Illuminate\Database\Seeder;

class VendorInventoryStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id' => 1,
                'code' => 'AVAILABLE',
                'name_en' => 'Available',
                'name_km' => 'មានលក់',
            ],
            [
                'id' => 2,
                'code' => 'OUT_OF_STOCK',
                'name_en' => 'Out of Stock',
                'name_km' => 'អស់ពីស្តុក',
            ],
            [
                'id' => 3,
                'code' => 'HIDDEN',
                'name_en' => 'Hidden',
                'name_km' => 'លាក់',
            ],
        ];

        foreach ($types as $type) {
            VendorInventoryStatus::create($type);
        }
    }
}
