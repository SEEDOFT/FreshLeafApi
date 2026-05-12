<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id' => NotificationType::ORDER_UPDATE_ID,
                'name_en' => 'Order Update',
                'name_km' => 'ការបញ្ជាទិញ',
            ],
            [
                'id' => NotificationType::PROMOTION_ID,
                'name_en' => 'Promotion',
                'name_km' => 'ប្រូម៉ូសិន',
            ],
            [
                'id' => NotificationType::SYSTEM_ID,
                'name_en' => 'System',
                'name_km' => 'ប្រព័ន្ធ',
            ],
        ];

        foreach ($types as $type) {
            NotificationType::create($type);
        }
    }
}
