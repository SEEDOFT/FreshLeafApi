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
                'id' => 1,
                'code' => 'PENDING',
                'name_en' => 'Pending',
                'name_km' => 'រង់ចាំ',
            ],
            [
                'id' => 2,
                'code' => 'COMPLETED',
                'name_en' => 'Completed',
                'name_km' => 'បានបញ្ចប់',
            ],
            [
                'id' => 3,
                'code' => 'FAILED',
                'name_en' => 'Failed',
                'name_km' => 'បរាជ័យ',
            ],
        ];

        foreach ($types as $type) {
            WalletTransactionStatus::create($type);
        }
    }
}
