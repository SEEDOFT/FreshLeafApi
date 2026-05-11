<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'ORDER_UPDATE', 'name_en' => 'Order Update', 'name_km' => 'ការបញ្ជាទិញ'],
            ['id' => 2, 'code' => 'PROMOTION', 'name_en' => 'Promotion', 'name_km' => 'ប្រូម៉ូសិន'],
            ['id' => 3, 'code' => 'SYSTEM', 'name_en' => 'System', 'name_km' => 'ប្រព័ន្ធ'],
        ];

        foreach ($data as $d) {
            NotificationType::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
