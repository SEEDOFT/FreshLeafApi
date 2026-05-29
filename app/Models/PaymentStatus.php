<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
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
 * @property-read string $name
 * @property string|null $translated_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property Carbon|null $deleted_at
 */
#[Table('payment_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(PaymentStatusFactory::class)]
class PaymentStatus extends Model
{
    /** @use HasFactory<PaymentStatusFactory> */
    use HasFactory, SoftDeletes;

    public const int PENDING_ID = 1;

    public const int COMPLETED_ID = 2;

    public const int FAILED_ID = 3;

    public const int REFUNDED_ID = 4;

    public const string PENDING = 'PENDING';

    public const string COMPLETED = 'COMPLETED';

    public const string FAILED = 'FAILED';

    public const string REFUNDED = 'REFUNDED';

    /**
     * Get the English name (as the generic name).
     */
    public function getNameAttribute(): string
    {
        return $this->name_en;
    }

    /**
     * Get the translated name of the payment status.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::currentLocale()};
    }

    /**
     * Get the orders for the payment status.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_status_id', 'id');
    }

    /**
     * Get the payments for the status.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'status_id', 'id');
    }
}
