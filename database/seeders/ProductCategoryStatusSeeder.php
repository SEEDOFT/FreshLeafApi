<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategoryStatus;
use Illuminate\Database\Seeder;

class ProductCategoryStatusSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'code' => 'ACTIVE',
                'name_en' => 'Active',
                'name_km' => 'សកម្ម',
            ],
            [
                'id' => 2,
                'code' => 'INACTIVE',
                'name_en' => 'Inactive',
                'name_km' => 'អសកម្ម',
            ],
        ];

        foreach ($data as $d) {
            ProductCategoryStatus::create($d);
        }
    }
}
