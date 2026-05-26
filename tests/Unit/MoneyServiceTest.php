<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Services\MoneyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MoneyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ExchangeRate::clearRateCache();
    }

    public function test_it_calculates_discounted_money_with_bcmath_strings(): void
    {
        $this->assertSame('9.99', MoneyService::mul('3.33', '3'));
        $this->assertSame('7.50', MoneyService::discountUnitPrice('10.00', '25.00'));
        $this->assertSame('2.50', MoneyService::sub('10.00', '7.50'));
    }

    public function test_it_uses_latest_available_exchange_rate(): void
    {
        $this->createCurrencies();

        ExchangeRate::query()->create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4000.00000000',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        ExchangeRate::query()->create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4100.00000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('4100.00', MoneyService::convert('1.00', Currency::USD_ID, Currency::KHR_ID));
    }

    public function test_it_returns_dual_currency_totals_from_usd(): void
    {
        $this->createCurrencies();

        ExchangeRate::query()->create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4100.00000000',
        ]);

        $this->assertSame(
            ['USD' => '12.50', 'KHR' => '51250.00'],
            MoneyService::displayTotalsFromUsd('12.50')
        );
    }

    public function test_it_can_convert_from_inverse_exchange_rate(): void
    {
        $this->createCurrencies();

        ExchangeRate::query()->create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4000.00000000',
        ]);

        $this->assertSame('1.00', MoneyService::convert('4000.00', Currency::KHR_ID, Currency::USD_ID));
    }

    public function test_it_rejects_missing_cross_currency_rate(): void
    {
        $this->createCurrencies();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing exchange rate from currency 2 to 1.');

        MoneyService::convert('1.00', Currency::USD_ID, Currency::KHR_ID);
    }

    private function createCurrencies(): void
    {
        $usd = new Currency([
            'code' => Currency::USD,
            'name_en' => 'US Dollar',
            'name_km' => 'US Dollar',
            'symbol' => '$',
        ]);
        $usd->id = Currency::USD_ID;
        $usd->save();

        $khr = new Currency([
            'code' => Currency::KHR,
            'name_en' => 'Cambodian Riel',
            'name_km' => 'Cambodian Riel',
            'symbol' => 'KHR',
        ]);
        $khr->id = Currency::KHR_ID;
        $khr->save();
    }
}
