<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserWishlistItemStatus;
use Illuminate\Database\Seeder;

class UserWishlistItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
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

        foreach ($statuses as $status) {
            UserWishlistItemStatus::create($status);
        }
    }
}
