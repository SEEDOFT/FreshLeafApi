<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentMethodType;
use Illuminate\Database\Seeder;

class PaymentMethodTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['id' => PaymentMethodType::WALLET, 'code' => 'wallet', 'name' => 'Wallet'],
            ['id' => PaymentMethodType::CREDIT_DEBIT, 'code' => 'credit_debit', 'name' => 'Credit / Debit Card'],
            ['id' => PaymentMethodType::ABA, 'code' => 'aba', 'name' => 'ABA'],
            ['id' => PaymentMethodType::ACLEDA, 'code' => 'acleda', 'name' => 'ACLEDA'],
        ];

        PaymentMethodType::query()
            ->whereNotIn('id', \array_column($types, 'id'))
            ->delete();

        foreach ($types as $type) {
            PaymentMethodType::updateOrCreate(
                ['id' => $type['id']],
                ['code' => $type['code'], 'name' => $type['name']]
            );
        }
    }
}
