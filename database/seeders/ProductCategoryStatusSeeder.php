<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategoryStatus;
use Illuminate\Database\Seeder;

class ProductCategoryStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'id' => 1,
                'name_en' => 'Active',
                'name_km' => 'សកម្ម',
            ],
            [
                'id' => 2,
                'name_en' => 'Inactive',
                'name_km' => 'អសកម្ម',
            ],
        ];

        foreach ($statuses as $status) {
            ProductCategoryStatus::create($status);
        }
    }
}
