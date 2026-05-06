<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property float $balance
 * @property int $currency_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Table('wallets', key: 'id', keyType: 'int')]
#[Fillable([
    'user_id',
    'balance',
    'currency_id',
])]
#[UseFactory(WalletFactory::class)]
class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:4',
        ];
    }

    /**
     * Get the user that owns the wallet.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the currency associated with the wallet.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    /**
     * Get the wallet histories.
     *
     * @return HasMany<WalletHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(WalletHistory::class, 'wallet_id', 'id');
    }

    /**
     * Get the transactions for the wallet.
     *
     * @return HasMany<WalletTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id', 'id');
    }
}
