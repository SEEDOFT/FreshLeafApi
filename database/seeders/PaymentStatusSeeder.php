<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $statuses = [
                [
                    'id' => PaymentStatus::PENDING_ID,
                    'name_en' => 'Pending',
                    'name_km' => 'រង់ចាំ',
                ],
                [
                    'id' => PaymentStatus::COMPLETED_ID,
                    'name_en' => 'Completed',
                    'name_km' => 'បានទូទាត់',
                ],
                [
                    'id' => PaymentStatus::FAILED_ID,
                    'name_en' => 'Failed',
                    'name_km' => 'បរាជ័យ',
                ],
                [
                    'id' => PaymentStatus::REFUNDED_ID,
                    'name_en' => 'Refunded',
                    'name_km' => 'បានសងប្រាក់',
                ],
            ];

            foreach ($statuses as $status) {
                PaymentStatus::create($status);
            }
        });
    }
}
