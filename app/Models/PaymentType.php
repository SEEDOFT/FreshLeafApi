<?php

namespace App\Models;

use Database\Factories\PaymentTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
 * @method static \Database\Factories\PaymentTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class PaymentType extends Model
{
    /** @use HasFactory<PaymentTypeFactory> */
    use HasFactory;

    public const VISA = 1;

    public const MASTER_CARD = 2;

    public const UNION_PAY = 3;

    /**
     * Get the payments for the type.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
