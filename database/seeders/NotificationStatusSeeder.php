<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NotificationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $statuses = [
                [
                    'id' => NotificationStatus::UNREAD_ID,
                    'name_en' => 'Unread',
                    'name_km' => 'មិនទាន់អាន',
                ],
                [
                    'id' => NotificationStatus::READ_ID,
                    'name_en' => 'Read',
                    'name_km' => 'បានអាន',
                ],
            ];

            foreach ($statuses as $status) {
                NotificationStatus::create($status);
            }
        });
    }
}
