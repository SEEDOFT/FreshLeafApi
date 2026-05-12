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
        $statuses = [
            [
                'id' => UserStatus::PENDING_ID,
                'name_en' => 'PENDING',
                'name_km' => 'រង់ចាំ',
            ],
            [
                'id' => UserStatus::ACTIVE_ID,
                'name_en' => 'ACTIVE',
                'name_km' => 'សកម្ម',
            ],
            [
                'id' => UserStatus::INACTIVE_ID,
                'name_en' => 'INACTIVE',
                'name_km' => 'អសកម្ម',
            ],
            [
                'id' => UserStatus::DELETED_ID,
                'name_en' => 'DELETED',
                'name_km' => 'លុបចោល',
            ],
        ];

        foreach ($statuses as $status) {
            UserStatus::create($status);
        }
    }
}
