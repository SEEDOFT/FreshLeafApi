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
 * @property int $wallet_id
 * @property int $user_id
 * @property int $currency_id
 * @property float $balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Currency $currency
 * @property-read Wallet $wallet
 * @property Carbon|null $deleted_at
 */
#[Table('wallet_histories', key: 'id', keyType: 'int')]
#[Fillable(['wallet_id', 'user_id', 'currency_id', 'balance'])]
class WalletHistory extends Model
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
            'balance' => 'decimal:4',
        ];
    }

    /**
     * Belong to Wallet
     *
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
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
}
