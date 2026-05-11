<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'COD', 'name_en' => 'Cash on Delivery', 'name_km' => 'សាច់ប្រាក់នៅពេលដឹកជញ្ជូន'],
            ['id' => 2, 'code' => 'BANK_TRANSFER', 'name_en' => 'Bank Transfer', 'name_km' => 'ផ្ទេរតាមធនាគារ'],
            ['id' => 3, 'code' => 'WALLET', 'name_en' => 'Wallet', 'name_km' => 'កាបូបលុយ'],
        ];

        foreach ($data as $d) {
            PaymentType::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
