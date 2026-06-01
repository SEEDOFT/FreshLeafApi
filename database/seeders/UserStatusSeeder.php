<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $statuses = [
                [
                    'id' => UserStatus::PENDING_ID,
                    'name_en' => 'PENDING',
                    'name_km' => 'រង់ចាំការផ្ទៀងផ្ទាត់',
                ],
                [
                    'id' => UserStatus::ACTIVE_ID,
                    'name_en' => 'ACTIVE',
                    'name_km' => 'កំពុងប្រើប្រាស់',
                ],
                [
                    'id' => UserStatus::INACTIVE_ID,
                    'name_en' => 'INACTIVE',
                    'name_km' => 'បានផ្អាកសកម្មភាព',
                ],
                [
                    'id' => UserStatus::REJECTED_ID,
                    'name_en' => 'REJECTED',
                    'name_km' => 'មិនអនុញ្ញាត',
                ],
                [
                    'id' => UserStatus::DELETED_ID,
                    'name_en' => 'DELETED',
                    'name_km' => 'បានលុបចោល',
                ],
            ];

            foreach ($statuses as $status) {
                UserStatus::create($status);
            }
        });
    }
}
