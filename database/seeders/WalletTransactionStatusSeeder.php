<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WalletTransactionStatus;
use Illuminate\Database\Seeder;

class WalletTransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id' => WalletTransactionStatus::PENDING_ID,
                'name_en' => 'Pending',
                'name_km' => 'រង់ចាំ',
            ],
            [
                'id' => WalletTransactionStatus::COMPLETED_ID,
                'name_en' => 'Completed',
                'name_km' => 'បានបញ្ចប់',
            ],
            [
                'id' => WalletTransactionStatus::FAILED_ID,
                'name_en' => 'Failed',
                'name_km' => 'បរាជ័យ',
            ],
            [
                'id' => WalletTransactionStatus::CANCELLED_ID,
                'name_en' => 'Cancelled',
                'name_km' => 'បានលុបចោល',
            ],
        ];

        foreach ($types as $type) {
            WalletTransactionStatus::create($type);
        }
    }
}
