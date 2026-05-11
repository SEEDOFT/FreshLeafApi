<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserCartType;
use Illuminate\Database\Seeder;

class UserCartTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'STANDARD', 'name_en' => 'Standard', 'name_km' => 'ទូទៅ'],
            ['id' => 2, 'code' => 'BULK', 'name_en' => 'Bulk', 'name_km' => 'លក់ដុំ'],
        ];

        foreach ($data as $d) {
            UserCartType::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
