<?php

namespace App\Http\Requests\User\PaymentMethod;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'payment_method_type_id' => ['required', 'exists:payment_method_types,id'],
            'card_holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'min:12', 'max:19'],
            'expiry_month' => ['required', 'integer', 'min:1', 'max:12'],
            'expiry_year' => ['required', 'integer', 'min:'.date('Y'), 'max:'.date('Y') + 20],
            'cvv' => ['required', 'string', 'min:3', 'max:4'],
            'is_default' => ['sometimes', 'boolean'],
            'billing_address' => ['sometimes', 'string', 'max:255'],
            'billing_city' => ['sometimes', 'string', 'max:255'],
            'billing_state' => ['sometimes', 'string', 'max:255'],
            'billing_zip_code' => ['sometimes', 'string', 'max:20'],
        ];
    }
}
