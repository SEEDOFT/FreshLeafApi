<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'USD', 'name_en' => 'US Dollar', 'name_km' => 'ដុល្លារអាមេរិក', 'symbol' => '$'],
            ['id' => 2, 'code' => 'KHR', 'name_en' => 'Cambodian Riel', 'name_km' => 'រៀល', 'symbol' => '៛'],
        ];

        foreach ($data as $d) {
            Currency::create($d);
        }
    }
}
