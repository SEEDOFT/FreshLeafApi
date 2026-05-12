<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'id' => OrderStatus::PENDING_ID,
                'name_en' => 'Pending',
                'name_km' => 'រង់ចាំ',
            ],
            [
                'id' => OrderStatus::CONFIRMED_ID,
                'name_en' => 'Confirmed',
                'name_km' => 'បញ្ជាក់',
            ],
            [
                'id' => OrderStatus::PREPARING_ID,
                'name_en' => 'Preparing',
                'name_km' => 'ត្រៀមដឹកជញ្ជូន',
            ],
            [
                'id' => OrderStatus::DELIVERED_ID,
                'name_en' => 'Delivered',
                'name_km' => 'បានទទួល',
            ],
            [
                'id' => OrderStatus::CANCELLED_ID,
                'name_en' => 'Cancelled',
                'name_km' => 'លុបចោល',
            ],
        ];

        foreach ($statuses as $status) {
            OrderStatus::create($status);
        }
    }
}
