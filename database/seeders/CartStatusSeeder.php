<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CartStatus;
use Illuminate\Database\Seeder;

class CartStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'id' => CartStatus::ACTIVE_ID,
                'name_en' => 'Active',
                'name_km' => 'សកម្ម',
            ],
            [
                'id' => CartStatus::REMOVED_ID,
                'name_en' => 'Removed',
                'name_km' => 'បានលុប',
            ],
            [
                'id' => CartStatus::CHECKED_OUT_ID,
                'name_en' => 'Checked Out',
                'name_km' => 'បានបញ្ជាទិញ',
            ],
        ];

        foreach ($statuses as $status) {
            CartStatus::create($status);
        }
    }
}
