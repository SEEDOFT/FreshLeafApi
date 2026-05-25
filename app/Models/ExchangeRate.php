<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
 * @property \Illuminate\Support\Carbon|null $deleted_at
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
     * Get the rate between two currencies by their codes.
     */
    public static function getRate(string $fromCode, string $toCode): float
    {
        $cacheKey = "{$fromCode}_{$toCode}";

        if (isset(self::$rateCache[$cacheKey])) {
            return self::$rateCache[$cacheKey];
        }

        /** @var float|string|null $rate */
        $rate = self::query()
            ->whereHas('fromCurrency', static fn (Builder $query) => $query->where('code', $fromCode))
            ->whereHas('toCurrency', static fn (Builder $query) => $query->where('code', $toCode))
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
