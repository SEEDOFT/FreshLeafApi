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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property-read string $code
 * @property-read string|null $translated_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WalletTransaction[] $transactions
 * @property Carbon|null $deleted_at
 */
#[Table('wallet_transaction_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(WalletTransactionStatusFactory::class)]
class WalletTransactionStatus extends Model
{
    /** @use HasFactory<WalletTransactionStatusFactory> */
    use HasFactory;

    use SoftDeletes;

    public const int PENDING_ID = 1;

    public const int COMPLETED_ID = 2;

    public const int FAILED_ID = 3;

    public const int CANCELLED_ID = 4;

    public const string PENDING = 'PENDING';

    public const string COMPLETED = 'COMPLETED';

    public const string FAILED = 'FAILED';

    public const string CANCELLED = 'CANCELLED';

    /**
     * Get translated name of wallet transaction status.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::getLocale()};
    }

    /**
     * Get status code.
     */
    public function getCodeAttribute(): string
    {
        return match ($this->id) {
            self::PENDING_ID => self::PENDING,
            self::COMPLETED_ID => self::COMPLETED,
            self::FAILED_ID => self::FAILED,
            self::CANCELLED_ID => self::CANCELLED,
            default => 'UNKNOWN',
        };
    }

    /**
     * Get the transactions for this status.
     *
     * @return HasMany<WalletTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(
            WalletTransaction::class,
            'wallet_transaction_status_id',
            'id'
        );
    }
}
