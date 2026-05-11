<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserCartStatus;
use Illuminate\Database\Seeder;

class UserCartStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'code' => 'ACTIVE',
                'name_en' => 'Active',
                'name_km' => 'សកម្ម',
            ],
            [
                'id' => 2,
                'code' => 'CONVERTED',
                'name_en' => 'Converted to Order',
                'name_km' => 'បានប្តូរជាការបញ្ជាទិញ',
            ],
            [
                'id' => 3,
                'code' => 'EXPIRED',
                'name_en' => 'Expired',
                'name_km' => 'ផុតកំណត់',
            ],
        ];

        foreach ($data as $d) {
            UserCartStatus::create($d);
        }
    }
}
