<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('wallets', key: 'id')]
#[Fillable([
    'walletable_type',
    'walletable_id',
    'balance',
    'currency_id',
    'is_default',
])]
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
            'balance' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the owning walletable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function walletable(): MorphTo
    {
        return $this->morphTo('walletable', 'walletable_type', 'walletable_id');
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
