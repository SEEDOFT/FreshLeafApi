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
 * @property int $payment_id
 * @property int $payment_status_id
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Payment $payment
 * @property-read PaymentStatus $status
 */
#[Table('payment_histories', key: 'id')]
#[Fillable([
    'payment_id',
    'payment_status_id',
    'notes',
])]
class PaymentHistory extends Model
{
    use SoftDeletes;

    /**
     * Get the payment this history belongs to.
     *
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }

    /**
     * Get the payment status for this history.
     *
     * @return BelongsTo<PaymentStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id', 'id');
    }
}
