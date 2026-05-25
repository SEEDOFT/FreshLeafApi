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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property-read string|null $translated_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PaymentMethod> $paymentMethods
 * @property-read int|null $payment_methods_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
#[Table('payment_method_types', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(PaymentMethodTypeFactory::class)]
class PaymentMethodType extends Model
{
    use SoftDeletes;

    /** @use HasFactory<PaymentMethodTypeFactory> */
    use HasFactory;

    public const int WALLET_ID = 1;

    public const int CREDIT_DEBIT_ID = 2;

    public const int ABA_ID = 3;

    public const int ACLEDA_ID = 4;

    public const int COD_ID = 5;

    public const string WALLET = 'WALLET';

    public const string CREDIT_DEBIT = 'CREDIT_DEBIT';

    public const string ABA = 'ABA';

    public const string ACLEDA = 'ACLEDA';

    public const string COD = 'COD';

    /**
     * @return list<int>
     */
    public static function coreTypeIds(): array
    {
        return [
            self::WALLET_ID,
            self::CREDIT_DEBIT_ID,
            self::ABA_ID,
            self::ACLEDA_ID,
            self::COD_ID,
        ];
    }

    /**
     * Get the translated name attribute.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::getLocale()};
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
