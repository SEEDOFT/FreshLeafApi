<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

#[Table('wallets', key: 'id')]
#[Fillable([
    'user_id',
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
     * Get the user that owns the wallet.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

    /**
     * Get the wallet histories.
     *
     * @return HasMany<WalletHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(WalletHistory::class, 'wallet_id', 'id');
    }

    /**
     * @return array<string, mixed>
     */
    protected static function booted(): void
    {
        static::created(static function (Wallet $wallet): void {
            $wallet->recordHistory(
                action: WalletHistory::ACTION_CREATED,
                amountBefore: '0.0000',
                amountAfter: (string) $wallet->balance,
                performedByUserId: Auth::id(),
                description: 'Wallet created'
            );
        });

        static::updated(static function (Wallet $wallet): void {
            if (! $wallet->wasChanged('balance')) {
                return;
            }

            $wallet->recordHistory(
                action: WalletHistory::ACTION_BALANCE_UPDATED,
                amountBefore: (string) $wallet->getOriginal('balance'),
                amountAfter: (string) $wallet->balance,
                performedByUserId: Auth::id(),
                description: 'Wallet balance updated'
            );
        });
    }

    private function recordHistory(
        string $action,
        string $amountBefore,
        string $amountAfter,
        ?int $performedByUserId = null,
        ?string $description = null,
    ): void {
        $before = (float) $amountBefore;
        $after = (float) $amountAfter;
        $change = $after - $before;

        $this->histories()->create([
            'wallet_id' => $this->id,
            'user_id' => $this->user_id,
            'currency_id' => $this->currency_id,
            'action' => $action,
            'amount_before' => \number_format($before, 4, '.', ''),
            'amount_change' => \number_format($change, 4, '.', ''),
            'amount_after' => \number_format($after, 4, '.', ''),
            'performed_by_user_id' => $performedByUserId,
            'description' => $description,
        ]);
    }
}
