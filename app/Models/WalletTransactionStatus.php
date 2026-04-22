<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WalletTransactionStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('wallet_transaction_statuses', key: 'id')]
#[Fillable(['code', 'name'])]
class WalletTransactionStatus extends Model
{
    /** @use HasFactory<WalletTransactionStatusFactory> */
    use HasFactory;

    public const int PENDING = 1;

    public const int COMPLETED = 2;

    public const int FAILED = 3;

    public const int CANCELLED = 4;

    /**
     * Get the transactions for this status.
     *
     * @return HasMany<WalletTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_transaction_status_id', 'id');
    }
}
