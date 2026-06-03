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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property-read string|null $translated_name
 * @property-read WalletTransaction[] $transactions
 * @property Carbon|null $deleted_at
 */
#[Table('wallet_transaction_types', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(WalletTransactionTypeFactory::class)]
class WalletTransactionType extends Model
{
    /** @use HasFactory<WalletTransactionTypeFactory> */
    use HasFactory, SoftDeletes;

    public const int DEPOSIT_ID = 1;

    public const int WITHDRAWAL_ID = 2;

    public const int PAYMENT_ID = 3;

    public const int REFUND_ID = 4;

    public const string DEPOSIT = 'DEPOSIT';

    public const string WITHDRAWAL = 'WITHDRAWAL';

    public const string PAYMENT = 'PAYMENT';

    public const string REFUND = 'REFUND';

    /**
     * Get translated name of wallet transaction type.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::getLocale()};
    }

    /**
     * Get the transactions for this type.
     *
     * @return HasMany<WalletTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(
            WalletTransaction::class,
            'wallet_transaction_type_id',
            'id'
        );
    }
}
