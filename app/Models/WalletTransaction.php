<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WalletTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $wallet_id
 * @property int $wallet_transaction_type_id
 * @property int $wallet_transaction_status_id
 * @property float $amount
 * @property int|null $payment_method_id
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property string|null $description
 * @property Carbon|null $transaction_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Wallet $wallet
 * @property-read WalletTransactionType $type
 * @property-read WalletTransactionStatus $status
 * @property-read Collection<int, WalletTransactionHistory> $histories
 * @property Carbon|null $deleted_at
 */
#[Table('wallet_transactions', key: 'id', keyType: 'int')]
#[Fillable([
    'wallet_id',
    'wallet_transaction_type_id',
    'wallet_transaction_status_id',
    'amount',
    'payment_method_id',
    'reference_id',
    'reference_type',
    'description',
    'transaction_date',
])]
#[UseFactory(WalletTransactionFactory::class)]
class WalletTransaction extends Model
{
    /** @use HasFactory<WalletTransactionFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'wallet_id' => 'integer',
            'wallet_transaction_type_id' => 'integer',
            'wallet_transaction_status_id' => 'integer',
            'transaction_date' => 'datetime',
            'reference_code' => 'string',
        ];
    }

    /**
     * Get the wallet associated with the transaction.
     *
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    /**
     * Get the type of the transaction.
     *
     * @return BelongsTo<WalletTransactionType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(
            WalletTransactionType::class,
            'wallet_transaction_type_id',
            'id'
        );
    }

    /**
     * Get the status of the transaction.
     *
     * @return BelongsTo<WalletTransactionStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(
            WalletTransactionStatus::class,
            'wallet_transaction_status_id',
            'id'
        );
    }

    /**
     * Get the history for this transaction.
     *
     * @return HasMany<WalletTransactionHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(
            WalletTransactionHistory::class,
            'wallet_transaction_id',
            'id'
        );
    }
}
