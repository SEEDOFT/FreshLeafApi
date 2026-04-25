<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Fresh Produce'],
            ['name' => 'Staple'],
            ['name' => 'Protein'],
        ];

        foreach ($types as $type) {
            ProductType::query()->updateOrCreate(
                ['name' => $type['name']],
                ['name' => $type['name']]
            );
        }
    }
}
