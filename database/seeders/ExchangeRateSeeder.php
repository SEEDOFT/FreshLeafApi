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
        $usd = Currency::where('code', 'USD')->first();
        $khr = Currency::where('code', 'KHR')->first();

        if ($usd && $khr) {
            ExchangeRate::updateOrCreate(
                [
                    'from_currency_id' => $usd->id,
                    'to_currency_id' => $khr->id,
                ],
                [
                    'rate' => 4100.0000,
                ]
            );
        }
    }
}
