<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $payment_method_type_id
 * @property int $payment_method_status_id
 * @property string $label
 * @property string $card_holder_name
 * @property string $card_number
 * @property int $expiry_month
 * @property int $expiry_year
 * @property string $cvv
 * @property bool $is_default
 * @property string $billing_address
 * @property string $billing_city
 * @property string $billing_state
 * @property string $billing_zip_code
 * @property string|null $bank_name
 * @property string|null $account_name
 * @property string|null $account_number
 * @property string|null $qr_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @property-read PaymentMethodType $type
 * @property-read PaymentMethodStatus $status
 *
 * @method static Builder<static>|PaymentMethod active()
 * @method static Builder<static>|PaymentMethod isType()
 */
#[Table('payment_methods', key: 'id', keyType: 'int')]
#[Fillable([
    'user_id',
    'payment_method_type_id',
    'payment_method_status_id',
    'label',
    'card_holder_name',
    'card_number',
    'expiry_month',
    'expiry_year',
    'cvv',
    'is_default',
    'billing_address',
    'billing_city',
    'billing_state',
    'billing_zip_code',
    'bank_name',
    'account_name',
    'account_number',
    'qr_code',
])]
#[UseFactory(PaymentMethodFactory::class)]
class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory, SoftDeletes;

    public const int WALLET_ID = 1;

    public const int CREDIT_DEBIT_ID = 2;

    public const int ABA_ID = 3;

    public const int ACLEDA_ID = 4;

    public const int COD_ID = 5;

    public const int WING_ID = 6;

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'card_holder_name' => 'encrypted',
            'card_number' => 'encrypted',
            'cvv' => 'encrypted',
            'expiry_month' => 'integer',
            'expiry_year' => 'integer',
            'is_default' => 'boolean',
            'billing_address' => 'string',
            'billing_city' => 'string',
            'billing_state' => 'string',
            'billing_zip_code' => 'string',
        ];
    }

    /**
     * Get the user that owns the payment method.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->where('users.user_type_id', UserType::CONSUMER_ID);
    }

    /**
     * Get the vendor that owns the payment method.
     *
     * @return BelongsTo<User, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->where('users.user_type_id', UserType::VENDOR_ID);
    }

    /**
     * Get the payment method type of the payment method.
     *
     * @return BelongsTo<PaymentMethodType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PaymentMethodType::class, 'payment_method_type_id', 'id');
    }

    /**
     * Get the payment method status of the payment method.
     *
     * @return BelongsTo<PaymentMethodStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PaymentMethodStatus::class, 'payment_method_status_id', 'id');
    }

    /**
     * Scope a query to only include active payment methods.
     *
     * @param  Builder<PaymentMethod>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('payment_method_status_id', PaymentMethodStatus::ACTIVE_ID);
    }

    /**
     * Scope a query to only include payment methods of the specified type.
     *
     * @param  Builder<PaymentMethod>  $query
     */
    #[Scope]
    protected function isType(Builder $query, int $typeId): void
    {
        $query->where('payment_method_type_id', $typeId);
    }
}
