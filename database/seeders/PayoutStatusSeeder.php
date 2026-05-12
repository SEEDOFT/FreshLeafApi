<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PayoutStatus;
use Illuminate\Database\Seeder;

class PayoutStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'id' => 1,
                'name_en' => 'Pending',
                'name_km' => 'រង់ចាំ',
            ],
            [
                'id' => 2,
                'name_en' => 'Paid',
                'name_km' => 'បានទូទាត់',
            ],
            [
                'id' => 3,
                'name_en' => 'Failed',
                'name_km' => 'បរាជ័យ',
            ],
        ];

        foreach ($statuses as $status) {
            PayoutStatus::create($status);
        }
    }
}
