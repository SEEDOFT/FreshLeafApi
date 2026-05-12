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
            [
                'id' => 1,
                'name_en' => 'Draft',
                'name_km' => 'ព្រាង',
            ],
            [
                'id' => 2,
                'name_en' => 'Published',
                'name_km' => 'បានផ្សព្វផ្សាយ',
            ],
            [
                'id' => 3,
                'name_en' => 'Archived',
                'name_km' => 'បានរក្សាទុក',
            ],
        ];

        foreach ($statuses as $status) {
            ProductStatus::create($status);
        }
    }
}
