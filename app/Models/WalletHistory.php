<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('wallet_histories', key: 'id')]
#[Fillable([
    'wallet_id',
    'user_id',
    'currency_id',
    'action',
    'amount_before',
    'amount_change',
    'amount_after',
    'performed_by_user_id',
    'reference_type',
    'reference_id',
    'description',
    'meta',
])]
class WalletHistory extends Model
{
    public const string ACTION_CREATED = 'created';

    public const string ACTION_BALANCE_UPDATED = 'balance_updated';

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_before' => 'decimal:4',
            'amount_change' => 'decimal:4',
            'amount_after' => 'decimal:4',
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id', 'id');
    }
}
