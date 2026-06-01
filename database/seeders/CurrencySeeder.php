<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $currencies = [
                [
                    'id' => Currency::KHR_ID,
                    'code' => Currency::KHR,
                    'name_en' => 'Cambodian Riel',
                    'name_km' => 'រៀល',
                    'symbol' => '៛',
                ],
                [
                    'id' => Currency::USD_ID,
                    'code' => Currency::USD,
                    'name_en' => 'US Dollar',
                    'name_km' => 'ដុល្លារអាមេរិក',
                    'symbol' => '$',
                ],
            ];

            foreach ($currencies as $currency) {
                Currency::create($currency);
            }
        });
    }
}
