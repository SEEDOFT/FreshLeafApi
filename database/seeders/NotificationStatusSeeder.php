<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NotificationStatus;
use Illuminate\Database\Seeder;

class NotificationStatusSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'UNREAD', 'name_en' => 'Unread', 'name_km' => 'មិនទាន់អាន'],
            ['id' => 2, 'code' => 'READ', 'name_en' => 'Read', 'name_km' => 'បានអាន'],
        ];

        foreach ($data as $d) {
            NotificationStatus::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
