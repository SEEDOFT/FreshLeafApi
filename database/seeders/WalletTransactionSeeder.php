<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Illuminate\Database\Seeder;

class WalletTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['id' => WalletTransactionType::TOP_UP, 'code' => 'top_up', 'name' => 'Top Up'],
            ['id' => WalletTransactionType::PURCHASE, 'code' => 'purchase', 'name' => 'Purchase'],
            ['id' => WalletTransactionType::REFUND, 'code' => 'refund', 'name' => 'Refund'],
            ['id' => WalletTransactionType::WITHDRAWAL, 'code' => 'withdrawal', 'name' => 'Withdrawal'],
        ];

        foreach ($types as $type) {
            WalletTransactionType::updateOrCreate(['id' => $type['id']], $type);
        }

        $statuses = [
            ['id' => WalletTransactionStatus::PENDING, 'code' => 'pending', 'name' => 'Pending'],
            ['id' => WalletTransactionStatus::COMPLETED, 'code' => 'completed', 'name' => 'Completed'],
            ['id' => WalletTransactionStatus::FAILED, 'code' => 'failed', 'name' => 'Failed'],
            ['id' => WalletTransactionStatus::CANCELLED, 'code' => 'cancelled', 'name' => 'Cancelled'],
        ];

        foreach ($statuses as $status) {
            WalletTransactionStatus::updateOrCreate(['id' => $status['id']], $status);
        }
    }
}
