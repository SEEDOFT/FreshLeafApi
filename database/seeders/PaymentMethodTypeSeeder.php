<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentMethodType;
use Illuminate\Database\Seeder;

class PaymentMethodTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'BANK', 'name_en' => 'Bank', 'name_km' => 'ធនាគារ'],
            ['id' => 2, 'code' => 'WALLET', 'name_en' => 'Wallet', 'name_km' => 'កាបូបលុយ'],
            ['id' => 3, 'code' => 'CASH', 'name_en' => 'Cash', 'name_km' => 'សាច់ប្រាក់'],
        ];

        foreach ($data as $d) {
            PaymentMethodType::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
