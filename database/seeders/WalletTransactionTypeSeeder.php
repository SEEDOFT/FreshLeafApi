<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WalletTransactionType;
use Illuminate\Database\Seeder;

class WalletTransactionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        WalletTransactionType::insertOrIgnore([
            [
                'id' => WalletTransactionType::DEPOSIT_ID,
                'name_en' => 'Deposit',
                'name_km' => 'ដាក់ប្រាក់',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => WalletTransactionType::WITHDRAWAL_ID,
                'name_en' => 'Withdrawal',
                'name_km' => 'ដកប្រាក់',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => WalletTransactionType::PAYMENT_ID,
                'name_en' => 'Payment',
                'name_km' => 'ការទូទាត់',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => WalletTransactionType::REFUND_ID,
                'name_en' => 'Refund',
                'name_km' => 'សងប្រាក់វិញ',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
