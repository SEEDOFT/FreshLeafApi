<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $symbol
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property Collection|Wallet[] $wallets
 */
#[Table('currencies', key: 'id')]
#[Fillable([
    'name',
    'code',
    'symbol',
])]
#[UseFactory(CurrencyFactory::class)]
class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory, SoftDeletes;

    public const string KHR = 'KHR';

    public const string USD = 'USD';

    /**
     * Get the wallets associated with the currency.
     *
     * @return HasMany<Wallet, $this>
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'currency_id', 'id');
    }
}
