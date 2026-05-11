<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserStatus;
use Illuminate\Database\Seeder;

class UserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'code' => 'PENDING',
                'name_en' => 'PENDING',
                'name_km' => 'រង់ចាំ',
            ],
            [
                'id' => 2,
                'code' => 'ACTIVE',
                'name_en' => 'ACTIVE',
                'name_km' => 'សកម្ម',
            ],
            [
                'id' => 3,
                'code' => 'INACTIVE',
                'name_en' => 'INACTIVE',
                'name_km' => 'អសកម្ម',
            ],
            [
                'id' => 4,
                'code' => 'DELETED',
                'name_en' => 'DELETED',
                'name_km' => 'លុបចោល',
            ],
        ];

        foreach ($data as $d) {
            UserStatus::create($d);
        }
    }
}
