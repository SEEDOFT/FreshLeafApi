<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $wallet_id
 * @property int $user_id
 * @property int $currency_id
 * @property float $balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Wallet $wallet
 */
#[Table('wallet_histories', key: 'id', keyType: 'int')]
#[Fillable(['wallet_id', 'user_id', 'currency_id', 'balance'])]
class WalletHistory extends Model
{
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
}
