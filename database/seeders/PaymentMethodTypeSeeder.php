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
            ['id' => PaymentMethodType::VISA, 'code' => 'visa', 'name' => 'Visa'],
            ['id' => PaymentMethodType::MASTER_CARD, 'code' => 'master_card', 'name' => 'MasterCard'],
            ['id' => PaymentMethodType::UNION_PAY, 'code' => 'union_pay', 'name' => 'UnionPay'],
            ['id' => PaymentMethodType::AMERICAN_EXPRESS, 'code' => 'american_express', 'name' => 'American Express'],
            ['id' => PaymentMethodType::DISCOVER, 'code' => 'discover', 'name' => 'Discover'],
            ['id' => PaymentMethodType::JCB, 'code' => 'jcb', 'name' => 'JCB'],
            ['id' => PaymentMethodType::DINERS_CLUB, 'code' => 'diners_club', 'name' => 'Diners Club'],
            ['id' => PaymentMethodType::PAYPAL, 'code' => 'paypal', 'name' => 'PayPal'],
            ['id' => PaymentMethodType::STRIPE, 'code' => 'stripe', 'name' => 'Stripe'],
        ];

        foreach ($types as $type) {
            PaymentMethodType::updateOrCreate(
                ['id' => $type['id']],
                ['code' => $type['code'], 'name' => $type['name']]
            );
        }
    }
}
