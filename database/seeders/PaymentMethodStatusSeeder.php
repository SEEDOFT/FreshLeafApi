<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentMethodStatus;
use Illuminate\Database\Seeder;

class PaymentMethodStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'id' => PaymentMethodStatus::ACTIVE_ID,
                'name_en' => 'Active',
                'name_km' => 'សកម្ម',
            ],
            [
                'id' => PaymentMethodStatus::INACTIVE_ID,
                'name_en' => 'Inactive',
                'name_km' => 'អសកម្ម',
            ],
            [
                'id' => PaymentMethodStatus::DELETE_ID,
                'name_en' => 'Deleted',
                'name_km' => 'លុបចោល',
            ],
        ];

        foreach ($statuses as $status) {
            PaymentMethodStatus::create($status);
        }
    }
}
