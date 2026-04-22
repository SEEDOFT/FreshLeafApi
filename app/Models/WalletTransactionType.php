<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WalletTransactionTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('wallet_transaction_types', key: 'id')]
#[Fillable(['code', 'name'])]
class WalletTransactionType extends Model
{
    /** @use HasFactory<WalletTransactionTypeFactory> */
    use HasFactory;

    public const int TOP_UP = 1;

    public const int PURCHASE = 2;

    public const int REFUND = 3;

    public const int WITHDRAWAL = 4;

    /**
     * Get the transactions for this type.
     *
     * @return HasMany<WalletTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_transaction_type_id', 'id');
    }
}
