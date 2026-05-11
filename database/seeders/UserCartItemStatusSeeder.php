<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserCartItemStatus;
use Illuminate\Database\Seeder;

class UserCartItemStatusSeeder extends Seeder
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
                'code' => 'REMOVED',
                'name_en' => 'Removed',
                'name_km' => 'បានលុប',
            ],
        ];

        foreach ($data as $d) {
            UserCartItemStatus::create($d);
        }
    }
}
