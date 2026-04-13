<?php

namespace App\Models;

use Database\Factories\PaymentTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 */
#[Table('payment_types', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(PaymentTypeFactory::class)]
class PaymentType extends Model
{
    use HasFactory;

    public const int VISA = 1;

    public const int MASTER_CARD = 2;

    public const int UNION_PAY = 3;

    /**
     * Get the payments for the type.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
