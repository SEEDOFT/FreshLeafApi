<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductStatus;
use Illuminate\Database\Seeder;

class ProductStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['id' => ProductStatus::ACTIVE, 'name' => 'Active'],
            ['id' => ProductStatus::INACTIVE, 'name' => 'Inactive'],
            ['id' => ProductStatus::DRAFT, 'name' => 'Draft'],
        ];

        foreach ($statuses as $status) {
            ProductStatus::query()->updateOrCreate(
                ['id' => $status['id']],
                ['name' => $status['name']]
            );
        }
    }
}
