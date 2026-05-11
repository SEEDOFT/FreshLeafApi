<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PayoutMethod;
use Illuminate\Database\Seeder;

class PayoutMethodSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'BANK_TRANSFER', 'name_en' => 'Bank Transfer', 'name_km' => 'ផ្ទេរតាមធនាគារ'],
            ['id' => 2, 'code' => 'WALLET', 'name_en' => 'Wallet', 'name_km' => 'កាបូបលុយ'],
        ];

        foreach ($data as $d) {
            PayoutMethod::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
