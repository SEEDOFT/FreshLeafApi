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
 * @property int $vendor_user_id
 * @property int $payout_status_id
 * @property int $payout_method_id
 * @property float $amount
 * @property string|null $transaction_reference
 * @property Carbon|null $processed_at
 * @property int|null $processed_by_admin_id
 * @property string|null $admin_notes
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
    'vendor_user_id',
    'payout_status_id',
    'payout_method_id',
    'amount',
    'transaction_reference',
    'processed_at',
    'processed_by_admin_id',
    'admin_notes',
])]
class Payout extends Model
{
    use SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
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
        return $this->belongsTo(User::class, 'vendor_user_id', 'id');
    }

    /**
     * @return BelongsTo<PayoutStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PayoutStatus::class, 'payout_status_id', 'id');
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
        return $this->belongsTo(User::class, 'processed_by_admin_id', 'id');
    }
}
