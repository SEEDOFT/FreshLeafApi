<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function str_repeat;
use function strlen;
use function substr;

/**
 * @mixin PaymentMethod
 */
class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'payment_method_type_id' => $this->payment_method_type_id,
            'payment_method_status_id' => $this->payment_method_status_id,
            'label' => $this->label,
            'card_holder_name' => $this->card_holder_name,
            'card_number' => self::maskCardNumber($this->card_number),
            'expiry_month' => $this->expiry_month,
            'expiry_year' => $this->expiry_year,
            'is_default' => $this->is_default,
            'cvv' => '***',
            'billing_address' => $this->billing_address,
            'billing_city' => $this->billing_city,
            'billing_state' => $this->billing_state,
            'billing_zip_code' => $this->billing_zip_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Mask the card number to show only last 4 digits.
     */
    private static function maskCardNumber(?string $cardNumber): ?string
    {
        if (! $cardNumber) {
            return null;
        }

        $length = strlen($cardNumber);
        if ($length <= 4) {
            return $cardNumber;
        }

        return str_repeat('*', $length - 4).substr($cardNumber, -4);
    }
}
