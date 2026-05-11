<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WalletTransactionTypeFactory;
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
#[Table('wallet_transaction_types', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['code', 'name_en', 'name_km'])]
#[UseFactory(WalletTransactionTypeFactory::class)]
class WalletTransactionType extends Model
{
    /** @use HasFactory<WalletTransactionTypeFactory> */
    use HasFactory;

    public const int DEPOSIT = 1;

    public const int WITHDRAWAL = 2;

    public const int PAYMENT = 3;

    public const int REFUND = 4;

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
