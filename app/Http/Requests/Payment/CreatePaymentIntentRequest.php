<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \auth()->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.50'],
            'currency' => ['sometimes', 'string', 'in:usd,eur,gbp,cad,aud'],
            'order_id' => ['sometimes', 'integer', 'exists:orders,id'],
        ];
    }
}
