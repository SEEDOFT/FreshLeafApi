<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentMethodType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $types = [
                [
                    'id' => PaymentMethodType::WALLET_ID,
                    'name_en' => 'Wallet',
                    'name_km' => 'កាបូបលុយ',
                ],
                [
                    'id' => PaymentMethodType::CREDIT_DEBIT_ID,
                    'name_en' => 'Credit / Debit Card',
                    'name_km' => 'កាតឥណទាន',
                ],
                [
                    'id' => PaymentMethodType::ABA_ID,
                    'name_en' => 'ABA Bank',
                    'name_km' => 'ធនាគារ ABA',
                ],
                [
                    'id' => PaymentMethodType::ACLEDA_ID,
                    'name_en' => 'ACLEDA Bank',
                    'name_km' => 'ធនាគារ ACLEDA',
                ],
                [
                    'id' => PaymentMethodType::COD_ID,
                    'name_en' => 'Cash On Delivery',
                    'name_km' => 'សាច់ប្រាក់ពេលទទួលទំនិញ',
                ],
            ];

            PaymentMethodType::insertOrIgnore($types);
        });
    }
}
