<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategoryStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategoryStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $statuses = [
                [
                    'id' => 1,
                    'name_en' => 'Enabled',
                    'name_km' => 'បានបើក',
                ],
                [
                    'id' => 2,
                    'name_en' => 'Disabled',
                    'name_km' => 'បានបិទ',
                ],
            ];

            foreach ($statuses as $status) {
                ProductCategoryStatus::create($status);
            }
        });
    }
}
