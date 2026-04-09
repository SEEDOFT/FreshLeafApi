<?php

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
            ['code' => 'fresh', 'name' => 'Fresh Produce'],
            ['code' => 'staple', 'name' => 'Staple'],
            ['code' => 'protein', 'name' => 'Protein'],
        ];

        foreach ($types as $type) {
            ProductType::query()->updateOrCreate(
                ['code' => $type['code']],
                ['name' => $type['name']]
            );
        }
    }
}
