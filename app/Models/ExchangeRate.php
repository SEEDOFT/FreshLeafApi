<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * @property int $id
 * @property int $from_currency_id
 * @property int $to_currency_id
 * @property string $rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Currency $fromCurrency
 * @property-read Currency $toCurrency
 * @property-read ExchangeRateHistory[] $histories
 * @property Carbon|null $deleted_at
 */
#[Table('exchange_rates', key: 'id')]
#[Fillable([
    'from_currency_id',
    'to_currency_id',
    'rate',
])]
class ExchangeRate extends Model
{
    use SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
        ];
    }

    /**
     * Static cache for rates to avoid multiple queries in a single request.
     *
     * @var array<string, string>
     */
    private static array $rateCache = [];

    /**
     * Get the rate between two currencies by their codes.
     */
    public static function getRate(int $fromCurrencyId, int $toCurrencyId): string
    {
        $cacheKey = "{$fromCurrencyId}_{$toCurrencyId}";

        if ($fromCurrencyId === $toCurrencyId) {
            return '1.00000000';
        }

        if (isset(self::$rateCache[$cacheKey])) {
            return self::$rateCache[$cacheKey];
        }

        /** @var string|null $rate */
        $rate = self::query()
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->latest('updated_at')
            ->latest('id')
            ->value('rate');

        if ($rate !== null) {
            return self::$rateCache[$cacheKey] = MoneyService::rate($rate);
        }

        /** @var string|null $inverseRate */
        $inverseRate = self::query()
            ->where('from_currency_id', $toCurrencyId)
            ->where('to_currency_id', $fromCurrencyId)
            ->latest('updated_at')
            ->latest('id')
            ->value('rate');

        if ($inverseRate === null) {
            throw new RuntimeException("Missing exchange rate from currency {$fromCurrencyId} to {$toCurrencyId}.");
        }

        return self::$rateCache[$cacheKey] = MoneyService::div('1', MoneyService::rate($inverseRate), 8);
    }

    public static function clearRateCache(): void
    {
        self::$rateCache = [];
    }

    /**
     * Get the base currency.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id', 'id');
    }

    /**
     * Get the target currency.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id', 'id');
    }

    /**
     * Get the history of changes for this rate.
     *
     * @return HasMany<ExchangeRateHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ExchangeRateHistory::class, 'exchange_rate_id', 'id');
    }
}
