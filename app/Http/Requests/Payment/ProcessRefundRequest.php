<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ProcessRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'payment_intent_id' => ['required', 'string'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
        ];
    }
}
