<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentMethodTypeFactory;
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
 * @property-read Collection<int, PaymentMethod> $paymentMethods
 * @property-read int|null $payment_methods_count
 */
#[Table('payment_method_types', key: 'id')]
#[Fillable(['code', 'name'])]
#[UseFactory(PaymentMethodTypeFactory::class)]
class PaymentMethodType extends Model
{
    /** @use HasFactory<PaymentMethodTypeFactory> */
    use HasFactory;

    public const int WALLET = 1;

    public const int CREDIT_DEBIT = 2;

    public const int ABA = 3;

    public const int ACLEDA = 4;

    /**
     * @return array<int>
     */
    public static function coreTypeIds(): array
    {
        return [
            self::WALLET,
            self::CREDIT_DEBIT,
            self::ABA,
            self::ACLEDA,
        ];
    }

    /**
     * Get the payment methods for the type.
     *
     * @return HasMany<PaymentMethod, $this>
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'payment_method_type_id', 'id');
    }
}
