<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PayoutMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayoutMethodSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(static function () {
            $methods = [
                [
                    'id' => 1,
                    'name_en' => 'Bank Transfer',
                    'name_km' => 'ការផ្ទេរតាមធនាគារ',
                ],
                [
                    'id' => 2,
                    'name_en' => 'Wallet',
                    'name_km' => 'កាបូបលុយ',
                ],
                [
                    'id' => 3,
                    'name_en' => 'Cash',
                    'name_km' => 'សាច់ប្រាក់',
                ],
            ];

            foreach ($methods as $method) {
                PayoutMethod::create($method);
            }
        });
    }
}
