<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrderType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $types = [
                [
                    'id' => OrderType::STANDARD_ID,
                    'name_en' => 'Standard',
                    'name_km' => 'ស្តង់ដារ',
                ],
            ];

            foreach ($types as $type) {
                OrderType::create($type);
            }
        });
    }
}
