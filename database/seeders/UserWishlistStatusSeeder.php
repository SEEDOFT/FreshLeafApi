<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserWishlistStatus;
use Illuminate\Database\Seeder;

class UserWishlistStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id' => 1,
                'code' => 'ACTIVE',
                'name_en' => 'Active',
                'name_km' => 'សកម្ម',
            ],
            [
                'id' => 2,
                'code' => 'ARCHIVED',
                'name_en' => 'Archived',
                'name_km' => 'បានរក្សាទុក',
            ],
        ];

        foreach ($types as $type) {
            UserWishlistStatus::create($type);
        }
    }
}
