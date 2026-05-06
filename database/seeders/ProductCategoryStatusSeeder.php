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
            ['id' => ProductCategoryStatus::ACTIVE, 'code' => 'active', 'name' => 'Active'],
            ['id' => ProductCategoryStatus::INACTIVE, 'code' => 'inactive', 'name' => 'Inactive'],
        ];

        foreach ($statuses as $status) {
            ProductCategoryStatus::query()->updateOrCreate(
                ['id' => $status['id']],
                ['code' => $status['code'], 'name' => $status['name']]
            );
        }
    }
}
