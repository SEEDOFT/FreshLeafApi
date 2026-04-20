<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.50'],
            'currency' => ['sometimes', 'string', 'in:usd,eur,gbp,cad,aud'],
            'order_id' => ['sometimes', 'integer', 'exists:orders,id'],
        ];
    }
}
