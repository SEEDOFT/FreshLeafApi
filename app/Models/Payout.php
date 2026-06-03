<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_id
 * @property int $currency_id
 * @property int $status_id
 * @property int $payout_method_id
 * @property string $amount
 * @property string|null $payout_number
 * @property string|null $notes
 * @property string|null $transaction_reference
 * @property Carbon|null $processed_at
 * @property int|null $processed_by_admin_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $vendor
 * @property-read PayoutStatus $status
 * @property-read PayoutMethod $method
 * @property-read User|null $processor
 * @property Carbon|null $deleted_at
 */
#[Table('payouts', key: 'id')]
#[Fillable([
    'vendor_id',
    'currency_id',
    'payout_method_id',
    'amount',
    'status_id',
    'notes',
    'payout_number',
    'processed_at',
    'processed_by_admin_id',
    'transaction_reference',
])]
class Payout extends Model
{
    use SoftDeletes;

    public const int STATUS_PENDING = 1;

    public const int STATUS_PAID = 2;

    public const int STATUS_FAILED = 3;

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'processed_at' => 'datetime',
            'processed_by_admin_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id', 'id')
            ->where('users.user_type_id', UserType::VENDOR_ID);
    }

    /**
     * @return BelongsTo<PayoutStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PayoutStatus::class, 'status_id', 'id');
    }

    /**
     * @return BelongsTo<PayoutMethod, $this>
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(PayoutMethod::class, 'payout_method_id', 'id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_admin_id', 'id')
            ->where('users.user_type_id', UserType::ADMIN_ID);
    }
}
