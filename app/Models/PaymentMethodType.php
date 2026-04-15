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
 * @property-read Collection<int, UserPaymentMethod> $paymentMethods
 * @property-read int|null $payment_methods_count
 */
#[Table('payment_method_types', key: 'id')]
#[Fillable(['code', 'name'])]
#[UseFactory(PaymentMethodTypeFactory::class)]
class PaymentMethodType extends Model
{
    use HasFactory;

    public const int VISA = 1;

    public const int MASTER_CARD = 2;

    public const int UNION_PAY = 3;

    public const int AMERICAN_EXPRESS = 4;

    public const int DISCOVER = 5;

    public const int JCB = 6;

    public const int DINERS_CLUB = 7;

    public const int PAYPAL = 8;

    public const int STRIPE = 9;

    /**
     * Get the payment methods for the type.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(UserPaymentMethod::class, 'payment_method_type_id', 'id');
    }
}
