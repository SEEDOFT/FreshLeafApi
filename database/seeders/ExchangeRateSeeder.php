<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // KHR to USD
        $khrToUsd = ExchangeRate::create([
            'from_currency_id' => Currency::KHR_ID,
            'to_currency_id' => Currency::USD_ID,
            'rate' => '0.00025', // $1 = 4000 KHR => 1 KHR = 1/4000 = 0.00025 USD
        ]);

        $khrToUsd->histories()->create([
            'from_currency_id' => Currency::KHR_ID,
            'to_currency_id' => Currency::USD_ID,
            'rate' => '0.00025',
        ]);

        // USD to KHR
        $usdToKhr = ExchangeRate::create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4100.0000', // $1 = 4100 KHR
        ]);

        $usdToKhr->histories()->create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4100.0000',
        ]);
    }
}
