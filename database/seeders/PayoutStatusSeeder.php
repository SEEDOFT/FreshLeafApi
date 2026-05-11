<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PayoutStatus;
use Illuminate\Database\Seeder;

class PayoutStatusSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'PENDING', 'name_en' => 'Pending', 'name_km' => 'រង់ចាំ'],
            ['id' => 2, 'code' => 'PAID', 'name_en' => 'Paid', 'name_km' => 'បានទូទាត់'],
            ['id' => 3, 'code' => 'FAILED', 'name_en' => 'Failed', 'name_km' => 'បរាជ័យ'],
        ];

        foreach ($data as $d) {
            PayoutStatus::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
