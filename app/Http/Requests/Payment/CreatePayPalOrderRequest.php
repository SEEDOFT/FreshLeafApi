<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CreatePayPalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.50'],
            'currency' => ['sometimes', 'string', 'in:USD,EUR,GBP,CAD,AUD'],
            'order_id' => ['sometimes', 'integer', 'exists:orders,id'],
            'description' => ['sometimes', 'string', 'max:255'],
            'return_url' => ['required', 'url'],
            'cancel_url' => ['required', 'url'],
        ];
    }
}
