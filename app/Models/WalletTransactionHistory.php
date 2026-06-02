<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WalletTransactionHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $wallet_id
 * @property int $currency_id
 * @property int $wallet_transaction_type_id
 * @property int $wallet_transaction_status_id
 * @property float $amount
 * @property int|null $payment_method_id
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property Carbon|null $transaction_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WalletTransaction $transaction
 * @property-read Currency $currency
 * @property Carbon|null $deleted_at
 */
#[Table('wallet_transaction_histories', key: 'id', keyType: 'int')]
#[Fillable([
    'wallet_transaction_id',
    'wallet_id',
    'currency_id',
    'wallet_transaction_type_id',
    'wallet_transaction_status_id',
    'amount',
    'payment_method_id',
    'reference_id',
    'reference_type',
    'description',
    'transaction_date',
])]
#[UseFactory(WalletTransactionHistoryFactory::class)]
class WalletTransactionHistory extends Model
{
    /** @use HasFactory<WalletTransactionHistoryFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * Get the transaction associated with the history.
     *
     * @return BelongsTo<WalletTransaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            WalletTransaction::class,
            'wallet_transaction_id',
            'id'
        );
    }

    /**
     * Get the currency associated with the history entry.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
