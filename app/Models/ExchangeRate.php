<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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
 */
#[Table('exchange_rates', key: 'id')]
#[Fillable([
    'from_currency_id',
    'to_currency_id',
    'rate',
])]
class ExchangeRate extends Model
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
        ];
    }

    /**
     * Static cache for rates to avoid multiple queries in a single request.
     *
     * @var array<string, float>
     */
    private static array $rateCache = [];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(static function (ExchangeRate $exchangeRate): void {
            if ($exchangeRate->wasRecentlyCreated || $exchangeRate->wasChanged('rate')) {
                ExchangeRateHistory::create([
                    'exchange_rate_id' => $exchangeRate->id,
                    'changed_by_user_id' => Auth::id() ?? 1,
                    'from_currency_id' => $exchangeRate->from_currency_id,
                    'to_currency_id' => $exchangeRate->to_currency_id,
                    'rate' => $exchangeRate->rate,
                ]);
            }
        });
    }

    /**
     * Get the rate between two currencies by their codes.
     */
    public static function getRate(string $fromCode, string $toCode): float
    {
        $cacheKey = "{$fromCode}_{$toCode}";

        if (isset(self::$rateCache[$cacheKey])) {
            return self::$rateCache[$cacheKey];
        }

        /** @var float|string|null $rate */
        $rate = self::whereHas('fromCurrency', fn ($q) => $q->where('code', $fromCode))
            ->whereHas('toCurrency', fn ($q) => $q->where('code', $toCode))
            ->value('rate');

        return self::$rateCache[$cacheKey] = (float) ($rate ?? 1.0);
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
