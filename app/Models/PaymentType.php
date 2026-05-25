<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property Carbon|null $deleted_at
 */
#[Table('payment_types', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(PaymentTypeFactory::class)]
class PaymentType extends Model
{
    /** @use HasFactory<PaymentTypeFactory> */
    use HasFactory;

    use SoftDeletes;

    public const int ORDER_ID = 1;

    public const string ORDER = 'ORDER';

    /**
     * Get the payments for the type.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_type_id', 'id');
    }
}
