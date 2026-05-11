<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'PENDING', 'name_en' => 'Pending', 'name_km' => 'រង់ចាំ'],
            ['id' => 2, 'code' => 'CONFIRMED', 'name_en' => 'Confirmed', 'name_km' => 'បញ្ជាក់'],
            ['id' => 3, 'code' => 'SHIPPED', 'name_en' => 'Shipped', 'name_km' => 'បានដឹកជញ្ជូន'],
            ['id' => 4, 'code' => 'DELIVERED', 'name_en' => 'Delivered', 'name_km' => 'បានទទួល'],
            ['id' => 5, 'code' => 'CANCELLED', 'name_en' => 'Cancelled', 'name_km' => 'លុបចោល'],
        ];

        foreach ($data as $d) {
            OrderStatus::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
