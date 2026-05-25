<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentMethodStatusFactory;
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
 * @property-read int|null $paymentMethods_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
#[Table('payment_method_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(PaymentMethodStatusFactory::class)]
class PaymentMethodStatus extends Model
{
    use SoftDeletes;

    /** @use HasFactory<PaymentMethodStatusFactory> */
    use HasFactory;

    public const int ACTIVE_ID = 1;

    public const int INACTIVE_ID = 2;

    public const int DELETE_ID = 3;

    public const string ACTIVE = 'ACTIVE';

    public const string INACTIVE = 'INACTIVE';

    public const string DELETE = 'DELETED';

    /**
     * Get the translated name attribute.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::getLocale()};
    }

    /**
     * Get the payment methods for the status.
     *
     * @return HasMany<PaymentMethod, $this>
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'payment_method_status_id', 'id');
    }
}
