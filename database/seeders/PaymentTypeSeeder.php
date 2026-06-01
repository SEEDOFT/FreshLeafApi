<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $types = [
                [
                    'id' => PaymentType::ORDER_ID,
                    'name_en' => 'Order',
                    'name_km' => 'ការទូទាត់ការបញ្ជាទិញ',
                ],
            ];

            foreach ($types as $type) {
                PaymentType::create($type);
            }
        });
    }
}
