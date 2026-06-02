<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $exchange_rate_id
 * @property int|null $changed_by_user_id
 * @property int $from_currency_id
 * @property int $to_currency_id
 * @property string $rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ExchangeRate $exchangeRate
 * @property-read User|null $changer
 * @property-read Currency $fromCurrency
 * @property-read Currency $toCurrency
 * @property Carbon|null $deleted_at
 */
#[Table('exchange_rate_histories', key: 'id')]
#[Fillable([
    'exchange_rate_id',
    'changed_by_user_id',
    'from_currency_id',
    'to_currency_id',
    'rate',
])]
class ExchangeRateHistory extends Model
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
     * Get the exchange rate record.
     *
     * @return BelongsTo<ExchangeRate, $this>
     */
    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class, 'exchange_rate_id', 'id');
    }

    /**
     * Get the user who changed the rate.
     *
     * @return BelongsTo<User, $this>
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id', 'id')
            ->where('users.user_type_id', UserType::ADMIN_ID);
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id', 'id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id', 'id');
    }
}
