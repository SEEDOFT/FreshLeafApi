<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PayoutMethod;
use App\Models\PayoutStatus;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class CommissionSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'commission_rate_percentage'],
            [
                'value' => '10.00',
                'type' => 'decimal',
                'group' => 'financial',
            ]
        );

        $statuses = [
            ['name' => 'Pending', 'code' => 'pending'],
            ['name' => 'Processing', 'code' => 'processing'],
            ['name' => 'Completed', 'code' => 'completed'],
            ['name' => 'Failed', 'code' => 'failed'],
        ];

        foreach ($statuses as $status) {
            PayoutStatus::updateOrCreate(['code' => $status['code']], $status);
        }

        $methods = [
            ['name' => 'Bank Transfer', 'code' => 'bank_transfer'],
            ['name' => 'Internal Wallet', 'code' => 'wallet'],
            ['name' => 'Cash Payout', 'code' => 'cash'],
        ];

        foreach ($methods as $method) {
            PayoutMethod::updateOrCreate(['code' => $method['code']], $method);
        }
    }
}
