<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WalletTransactionStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name_en
 * @property string $name_km
 * @property-read WalletTransaction[] $transactions
 */
#[Table('wallet_transaction_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['code', 'name_en', 'name_km'])]
#[UseFactory(WalletTransactionStatusFactory::class)]
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
